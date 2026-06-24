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
        protected \App\Services\Billing\BillingService $billing,
        protected \App\Repositories\Ride\RatingRepository $ratings,
    ) {}

    // Free tier allows N categories; more requires an active service plan.
    // No-op while billing is disabled for the service module.
    protected function assertCategoryLimit(int $userId, array $categoryIds): void
    {
        $cfg = $this->billing->settings(\App\Constants\BillingModule::SERVICE);
        if (!$cfg->enforcement_enabled) {
            return;
        }
        if (count($categoryIds) > $cfg->free_limit && !$this->billing->activeSubscription($userId, \App\Constants\BillingModule::SERVICE)) {
            throw new ApiException("Your free plan allows {$cfg->free_limit} categories. Subscribe to offer more.", 402);
        }
    }

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

            $this->assertCategoryLimit($userId, $data['category_ids'] ?? []);

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

    public function browse(?int $categoryId, ?int $cityId, ?float $nearLat = null, ?float $nearLng = null, ?string $search = null)
    {
        return $this->repository->paginatedApproved($categoryId, $cityId, null, $nearLat, $nearLng, $search);
    }

    public function showPublic(int $id): ServiceProvider
    {
        $provider = $this->repository->approvedById($id);

        if (!$provider) {
            throw new ApiException('Service provider not found.', 404);
        }

        return $provider;
    }

    /** Reviews customers left for this provider (paginated, latest first). */
    public function reviews(int $id)
    {
        $provider = $this->repository->approvedById($id);

        if (!$provider) {
            throw new ApiException('Service provider not found.', 404);
        }

        return $this->ratings->paginatedReceivedForProvider($provider->user_id);
    }

    // ─── Admin ────────────────────────────────────────────────

    public function adminList(?string $status = null, ?int $perPage = null)
    {
        return $this->repository->paginatedForAdmin($status, $perPage);
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
