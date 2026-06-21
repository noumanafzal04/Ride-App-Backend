<?php

namespace App\Actions\Inspection;

use App\Actions\BaseAction\BaseAction;
use App\Exceptions\ApiException;
use App\Models\InspectionCategoryResult;
use App\Models\InspectionRequest;
use App\Repositories\Inspection\InspectionCategoryRepository;
use App\Repositories\Inspection\InspectionCategoryResultRepository;
use App\Mail\InspectionStatusMail;
use App\Repositories\Inspection\InspectionRequestRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class InspectionRequestAction extends BaseAction
{
    public function __construct(
        InspectionRequestRepository $repository,
        protected NotificationService $notifications,
        protected InspectionCategoryRepository $categories,
        protected InspectionCategoryResultRepository $results,
        protected \App\Services\Notification\AdminNotificationService $adminNotifications,
    ) {
        parent::__construct($repository, 'inspection_request');
    }

    // ─── Requester side ───────────────────────────────────────

    /**
     * Submit a new inspection request. $userId is null for guests.
     */
    public function submit(?int $userId, array $data): InspectionRequest
    {
        $created = DB::transaction(function () use ($userId, $data) {
            return $this->repository->create([
                'user_id'         => $userId,
                'tracking_token'  => $this->uniqueToken(),
                'name'            => $data['name'],
                'phone'           => $data['phone'],
                'email'           => $data['email'] ?? null,
                'car_make'        => $data['car_make'],
                'car_model'       => $data['car_model'],
                'car_year'        => $data['car_year'] ?? null,
                'variant'         => $data['variant'] ?? null,
                'registration_no' => $data['registration_no'] ?? null,
                'city_id'         => $data['city_id'] ?? null,
                'address'         => $data['address'] ?? null,
                'preferred_at'    => $data['preferred_at'] ?? null,
                'notes'           => $data['notes'] ?? null,
                'status'          => InspectionRequest::STATUS_PENDING,
            ]);
        });

        // Confirmation email (guests + logged-in, if an email was provided).
        $this->emailRequester(
            $created,
            'Request received',
            'We’ve received your car inspection request. Our team will review it and contact you shortly to schedule.',
        );

        // Notify admins of the new request.
        $car = trim(($created->car_make ?? '') . ' ' . ($created->car_model ?? ''));
        $this->adminNotifications->push(
            'inspection_new',
            'New inspection request',
            trim("New car inspection request" . ($car ? " for {$car}" : '') . " from {$created->name}."),
            ['inspection_request_id' => $created->id],
        );

        return $created;
    }

    public function listForUser(int $userId, ?int $limit = null)
    {
        return $this->repository->paginatedForUser($userId, $limit);
    }

    public function showForUser(int $userId, int $id): InspectionRequest
    {
        $request = $this->repository->findOrFail($id);

        if ($request->user_id !== $userId) {
            throw new ApiException('You do not have access to this request.', 403);
        }

        return $request->load([
            'city:id,name',
            'inspector:id,first_name,last_name',
            'categoryResults.category:id,name,slug,sort',
        ]);
    }

    /**
     * Requester cancels their own request (only while it hasn't started).
     */
    public function cancelForUser(int $userId, int $id): InspectionRequest
    {
        return DB::transaction(function () use ($userId, $id) {
            $request = $this->repository->findOrFail($id);

            if ($request->user_id !== $userId) {
                throw new ApiException('You do not have access to this request.', 403);
            }

            $cancellable = in_array($request->status, [
                InspectionRequest::STATUS_PENDING,
                InspectionRequest::STATUS_REVIEWING,
                InspectionRequest::STATUS_SCHEDULED,
            ], true);

            if (!$cancellable) {
                throw new ApiException('This request can no longer be cancelled.', 422);
            }

            $this->repository->update($id, ['status' => InspectionRequest::STATUS_CANCELLED]);
            $fresh = $this->repository->findOrFail($id);
            $this->notifyStatus($fresh); // confirmation (email + in-app)

            return $fresh->load(['city:id,name', 'inspector:id,first_name,last_name', 'categoryResults.category:id,name,slug,sort']);
        });
    }

    /**
     * Public status lookup by tracking code (guests, no auth).
     */
    public function trackByToken(string $token): InspectionRequest
    {
        $request = $this->repository->findOne(
            callback: fn($q) => $q->where('tracking_token', $token),
            relations: ['city:id,name', 'categoryResults.category:id,name,slug,sort'],
        );

        if (!$request) {
            throw new ApiException('No request found for that tracking code.', 404);
        }

        return $request;
    }

    // ─── Admin / team side ────────────────────────────────────

    public function adminList(?string $status = null, ?int $limit = null)
    {
        return $this->repository->paginatedForAdmin($status, $limit);
    }

    public function adminShow(int $id): InspectionRequest
    {
        return $this->repository->findOrFail($id)
            ->load([
                'city:id,name',
                'user:id,first_name,last_name,phone_number,email',
                'inspector:id,first_name,last_name',
                'categoryResults.category:id,name,slug,sort',
            ]);
    }

    /** The fixed category catalog (for the report form). */
    public function categoryCatalog()
    {
        return $this->categories->allOrdered();
    }

    /**
     * Save the category-level report. Upserts each category result, recomputes
     * the overall score + grade, marks the request completed, and notifies.
     *
     * @param array $items  [['category_id'=>int,'condition'=>string,'notes'=>?string], ...]
     */
    public function saveReport(int $id, array $items, ?string $comments = null): InspectionRequest
    {
        return DB::transaction(function () use ($id, $items, $comments) {
            $request = $this->repository->findOrFail($id);
            $wasCompleted = $request->status === InspectionRequest::STATUS_COMPLETED;

            foreach ($items as $item) {
                $this->results->upsertForRequest(
                    $id,
                    (int) $item['category_id'],
                    $item['condition'],
                    $item['notes'] ?? null,
                );
            }

            // Auto-score from the conditions just saved (na excluded).
            $conditions = $this->results->forRequest($id)->pluck('condition');
            $scored = $conditions->filter(fn($c) => isset(InspectionCategoryResult::WEIGHTS[$c]));
            $overallScore = $scored->isNotEmpty()
                ? round($scored->map(fn($c) => InspectionCategoryResult::WEIGHTS[$c])->avg(), 2)
                : null;

            $update = [
                'overall_score' => $overallScore,
                'overall_grade' => $this->gradeFor($overallScore),
                'status'        => InspectionRequest::STATUS_COMPLETED,
            ];
            if ($comments !== null) {
                $update['inspector_comments'] = $comments;
            }
            if (!$request->completed_at) {
                $update['completed_at'] = now();
            }

            $this->repository->update($id, $update);
            $fresh = $this->repository->findOrFail($id);

            if (!$wasCompleted) {
                $this->notifyStatus($fresh); // "Report ready"
            }

            return $fresh->load(['categoryResults.category:id,name,slug,sort', 'city:id,name']);
        });
    }

    protected function gradeFor(?float $score): ?string
    {
        if ($score === null) return null;
        return match (true) {
            $score >= 85 => 'A',
            $score >= 70 => 'B',
            $score >= 55 => 'C',
            $score >= 40 => 'D',
            default      => 'E',
        };
    }

    /**
     * Assign an inspector/team member to the request (moves pending → reviewing).
     */
    public function assign(int $id, int $inspectorId): InspectionRequest
    {
        return DB::transaction(function () use ($id, $inspectorId) {
            $request = $this->repository->findOrFail($id);

            $update = ['assigned_to' => $inspectorId];
            if ($request->status === InspectionRequest::STATUS_PENDING) {
                $update['status'] = InspectionRequest::STATUS_REVIEWING;
            }
            $this->repository->update($id, $update);

            $fresh = $this->repository->findOrFail($id);
            if ($fresh->status !== $request->status) {
                $this->notifyStatus($fresh);
            }

            return $fresh;
        });
    }

    /**
     * Update workflow status (+ schedule / report fields), then notify the
     * requester if it actually changed.
     */
    public function updateStatus(int $id, array $data): InspectionRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $request = $this->repository->findOrFail($id);
            $newStatus = $data['status'];

            $update = ['status' => $newStatus];

            if (array_key_exists('scheduled_at', $data))       $update['scheduled_at'] = $data['scheduled_at'];
            if (array_key_exists('overall_grade', $data))      $update['overall_grade'] = $data['overall_grade'];
            if (array_key_exists('overall_score', $data))      $update['overall_score'] = $data['overall_score'];
            if (array_key_exists('inspector_comments', $data)) $update['inspector_comments'] = $data['inspector_comments'];
            if (array_key_exists('admin_notes', $data))        $update['admin_notes'] = $data['admin_notes'];

            if ($newStatus === InspectionRequest::STATUS_COMPLETED && !$request->completed_at) {
                $update['completed_at'] = now();
            }

            $this->repository->update($id, $update);
            $fresh = $this->repository->findOrFail($id);

            if ($newStatus !== $request->status) {
                $this->notifyStatus($fresh);
            }

            return $fresh;
        });
    }

    /**
     * Notify the requester of a status change: in-app (logged-in users) AND
     * email (guests + logged-in, whenever an email was given). Guests have no
     * account, so email is their only channel.
     */
    protected function notifyStatus(InspectionRequest $request): void
    {
        [$title, $message] = match ($request->status) {
            InspectionRequest::STATUS_REVIEWING   => ['Request under review', 'Your car inspection request is under review — our team will contact you shortly.'],
            InspectionRequest::STATUS_SCHEDULED   => ['Inspection scheduled', 'Your car inspection has been scheduled. Check the details for the date and time.'],
            InspectionRequest::STATUS_IN_PROGRESS => ['Inspection in progress', 'Your car inspection has started.'],
            InspectionRequest::STATUS_COMPLETED   => ['Report ready', 'Your car inspection report is ready to view.'],
            InspectionRequest::STATUS_CANCELLED   => ['Request cancelled', 'Your car inspection request has been cancelled.'],
            default                               => ['Inspection update', 'Your car inspection request has been updated.'],
        };

        // In-app (logged-in only)
        if ($request->user_id) {
            $this->notifications->push(
                $request->user_id,
                'inspection_update',
                $title,
                $message,
                ['inspection_request_id' => $request->id, 'status' => $request->status],
            );
        }

        // Email (guests + logged-in, if an email was provided)
        $this->emailRequester($request, $title, $message);
    }

    /**
     * Fire-and-forget status email to the requester's given address. Failures
     * are logged and swallowed — they must never break the calling flow.
     */
    /** Short, human-readable, collision-safe tracking code. */
    protected function uniqueToken(): string
    {
        do {
            $token = Str::upper(Str::random(10));
        } while ($this->repository->findOne(callback: fn($q) => $q->where('tracking_token', $token)));

        return $token;
    }

    protected function emailRequester(InspectionRequest $request, string $heading, string $body): void
    {
        if (empty($request->email)) {
            return;
        }

        try {
            Mail::to($request->email)->send(new InspectionStatusMail($request, $heading, $body));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
