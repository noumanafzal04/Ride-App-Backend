<?php
// app/Actions/Driver/RidePostAction.php

namespace App\Actions\Driver;

use App\Actions\BaseAction\BaseAction;
use App\Constants\ResourceFields;
use App\Exceptions\ApiException;
use App\Events\RidePostCreated;
use App\Repositories\Driver\RidePostRepository;
use App\Repositories\Ride\RideAlertRepository;
use App\Services\Notification\NotificationService;
use App\Support\BuildsWithRelations;
use App\Models\RidePost;
use Throwable;

class RidePostAction extends BaseAction
{
    public function __construct(
        RidePostRepository $repository,
        protected RideAlertRepository $alerts,
        protected NotificationService $notifications,
    ) {
        parent::__construct($repository, 'ride_post');
    }

    /**
     * After a ride is posted, notify riders whose "notify me" alert matches the
     * route (and date). Indexed lookup; for very hot routes this would move to a
     * queued job, but it's fine synchronously at current scale.
     */
    protected function afterCreate($created, int $companyId, $data): void
    {
        $created->load(['fromCity:id,name', 'toCity:id,name']);
        $date = $created->departure_at ? $created->departure_at->toDateString() : null;

        // Live: push the new post to everyone browsing (they filter client-side).
        try {
            broadcast(new RidePostCreated($created));
        } catch (Throwable $e) {
            report($e);
        }

        $matches = $this->alerts->matchingForPost(
            $created->from_city_id,
            $created->to_city_id,
            $date,
            $created->driver_id,
        );

        $from = $created->fromCity?->name ?? 'your city';
        $to   = $created->toCity?->name ?? 'the destination';

        foreach ($matches as $alert) {
            $this->notifications->push(
                $alert->user_id,
                'ride_alert',
                'Ride available',
                "A ride from {$from} to {$to} was just posted.",
                ['ride_post_id' => $created->id],
            );
        }
    }

    // ── hooks ──────────────────────────────────────────────

    protected function beforeCreate(int $driverId, $data): array
    {
        $data['driver_id'] = $driverId;
        $data['status']    = 'active';

        // Driver chooses available seats for shared (validated ≤ capacity − 1); private = whole vehicle.
        $data['available_seats'] = $data['post_type'] === 'shared'
            ? (int) ($data['available_seats'] ?? 1)
            : null;

        return $data;
    }
    protected function beforeUpdate(int $driverId, $id, $data): array
    {
        // ownership guard
        $post = $this->repository->findOrFail($id);

        if ($post->driver_id !== $driverId) {
            throw new ApiException('You do not own this ride post.', 403);
        }

        // private posts never track seats
        if (isset($data['post_type']) && $data['post_type'] === 'private') {
            $data['available_seats'] = null;
        }

        return $data;
    }
    protected function beforeDestroy(int $driverId, $id): void
    {
        // ownership guard
        $post = $this->repository->findOrFail($id);

        if ($post->driver_id !== $driverId) {
            throw new ApiException('You do not own this ride post.', 403);
        }
    }
    // ── overrides using base repo methods ──────────────────

    public function all(int $driverId, ?array $filters = [])
    {
        return $this->repository->paginatedList(
            callback: function ($query) use ($driverId) {
                $query->where('driver_id', $driverId);
            },
            select: ResourceFields::RIDE_POST_LIST_FIELDS,
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                ['fromCity', 'toCity']
            ),
        );
    }

    /**
     * Count browseable posts newer than `after_id` for the rider's current
     * filters. Powers the app's "new rides available" banner via a cheap COUNT
     * query, so the full list is only re-fetched when the rider taps it.
     */
    public function newCount(int $userId, ?array $filters = []): int
    {
        $afterId = (int) ($filters['after_id'] ?? 0);

        // Nothing loaded yet → the banner is irrelevant (the list itself loads).
        if ($afterId <= 0) {
            return 0;
        }

        return $this->repository->newerBrowseableCount($userId, $afterId, $filters);
    }

    /**
     * Rider-facing browse: active, upcoming posts (excluding the viewer's own),
     * with driver + vehicle + cities. Optional filters: from_city_id, to_city_id, date.
     */
    public function browse(int $userId, ?array $filters = [])
    {
        return $this->repository->paginatedList(
            callback: function ($query) use ($userId, $filters) {
                $query->where('driver_id', '!=', $userId)
                    ->where('status', 'active')
                    ->where('departure_at', '>', now())
                    ->where(function ($q) {
                        $q->whereNull('available_seats')        // private — whole vehicle
                            ->orWhere('available_seats', '>', 0); // shared — seats left
                    })
                    // hide rides the rider already has an active booking on
                    ->whereDoesntHave('bookings', function ($q) use ($userId) {
                        $q->where('passenger_id', $userId)
                            ->whereIn('status', ['pending', 'accepted']);
                    });

                if (!empty($filters['from_city_id'])) {
                    $query->where('from_city_id', $filters['from_city_id']);
                }
                if (!empty($filters['to_city_id'])) {
                    $query->where('to_city_id', $filters['to_city_id']);
                }
                if (!empty($filters['date'])) {
                    $query->whereDate('departure_at', $filters['date']);
                }

                $query->orderBy('departure_at');
            },
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                [
                    'driver',
                    'driver.vehicles',
                    'driver.vehicles.vehicleModel',
                    'driver.vehicles.vehicleModel.make',
                    'fromCity',
                    'toCity',
                ]
            ),
        );
    }

    public function show($driverId, $id)
    {
        return $this->repository->findOne(
            callback: function ($query) use ($driverId, $id) {
                $query->where('id', $id)
                    ->where('driver_id', $driverId);
            },
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                [
                    'driver',
                    'driver.vehicles',
                    'driver.vehicles.vehicleModel',
                    'driver.vehicles.vehicleModel.make',
                    'fromCity',
                    'toCity',
                ]
            ),
        );
    }

    /**
     * Rider-facing detail of a single ride post (any driver), with driver + vehicle + cities.
     */
    public function showForRider($id)
    {
        return $this->repository->findOrFail(
            $id,
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                [
                    'driver',
                    'driver.vehicles',
                    'driver.vehicles.vehicleModel',
                    'driver.vehicles.vehicleModel.make',
                    'fromCity',
                    'toCity',
                ]
            ),
        );
    }
}
