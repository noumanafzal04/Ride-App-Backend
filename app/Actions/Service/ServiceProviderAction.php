<?php

namespace App\Actions\Service;

use App\Exceptions\ApiException;
use App\Models\ServiceProvider;
use App\Repositories\Service\ServiceProviderRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

class ServiceProviderAction
{
    public function __construct(
        protected ServiceProviderRepository $repository,
        protected NotificationService $notifications,
        protected \App\Services\Notification\AdminNotificationService $adminNotifications,
    ) {}

    /** The current user's provider profile (null if not registered). */
    public function forUser(int $userId): ?ServiceProvider
    {
        return $this->repository->forUser($userId);
    }

    /**
     * Register the user as a service provider — created `pending` for admin review.
     */
    public function register(int $userId, array $data): ServiceProvider
    {
        return DB::transaction(function () use ($userId, $data) {
            if ($this->repository->forUser($userId)) {
                throw new ApiException('You are already registered as a service provider.', 422);
            }

            $provider = $this->repository->create([
                'user_id'       => $userId,
                'business_name' => $data['business_name'],
                'city_id'       => $data['city_id'] ?? null,
                'area'          => $data['area'] ?? null,
                'phone'         => $data['phone'],
                'description'   => $data['description'] ?? null,
                'status'        => ServiceProvider::STATUS_PENDING,
            ]);

            $this->repository->syncCategories($provider, $data['category_ids']);

            $this->adminNotifications->push(
                'provider_new',
                'New service provider',
                "{$provider->business_name} registered and is awaiting approval.",
                ['provider_id' => $provider->id],
            );

            return $this->repository->forUser($userId);
        });
    }

    // ─── Public browse ────────────────────────────────────────

    public function browse(?int $categoryId, ?int $cityId)
    {
        return $this->repository->paginatedApproved($categoryId, $cityId);
    }

    public function showPublic(int $id): ServiceProvider
    {
        $provider = $this->repository->approvedById($id);

        if (!$provider) {
            throw new ApiException('Service provider not found.', 404);
        }

        return $provider;
    }

    // ─── Admin ────────────────────────────────────────────────

    public function adminList(?string $status = null)
    {
        return $this->repository->paginatedForAdmin($status);
    }

    public function setStatus(int $id, string $status): ServiceProvider
    {
        return DB::transaction(function () use ($id, $status) {
            $provider = $this->repository->findOrFail($id);
            $this->repository->update($id, ['status' => $status]);
            $fresh = $this->repository->findOrFail($id);

            if ($status === ServiceProvider::STATUS_APPROVED && $provider->status !== ServiceProvider::STATUS_APPROVED) {
                $this->notifications->push(
                    $fresh->user_id,
                    'service_provider_approved',
                    'You\'re approved',
                    'Your service provider account is approved — you can now receive service requests.',
                    [],
                );
            }

            return $fresh->load(['categories:id,name,slug,icon', 'city:id,name']);
        });
    }
}
