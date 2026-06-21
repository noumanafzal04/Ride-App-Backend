<?php

namespace App\Repositories\Service;

use App\Models\ServiceProvider;
use App\Repositories\BaseRepository;

class ServiceProviderRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ServiceProvider();
    }

    public function syncCategories(ServiceProvider $provider, array $categoryIds): void
    {
        $provider->categories()->sync($categoryIds);
    }

    public function incrementJobs(int $providerId): void
    {
        $this->model->newQuery()->where('id', $providerId)->increment('total_jobs');
    }

    public function setRatingAvg(int $providerId, $avg): void
    {
        $this->update($providerId, ['rating_avg' => $avg]);
    }

    public function forUser(int $userId): ?ServiceProvider
    {
        return $this->findOne(
            callback: fn($q) => $q->where('user_id', $userId),
            relations: ['categories:id,name,slug,icon', 'city:id,name'],
        );
    }

    /** Public browse: approved providers, optional category + city filters. */
    public function paginatedApproved(?int $categoryId, ?int $cityId, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($categoryId, $cityId) {
                $q->where('status', ServiceProvider::STATUS_APPROVED);
                if ($categoryId) {
                    $q->whereHas('categories', fn($c) => $c->where('category_id', $categoryId));
                }
                if ($cityId) {
                    $q->where('city_id', $cityId);
                }
                $q->orderByDesc('rating_avg')->orderByDesc('total_jobs');
            },
            relations: ['categories:id,name,slug,icon', 'city:id,name', 'user:id,first_name,last_name'],
            limit: $limit,
        );
    }

    public function approvedById(int $id): ?ServiceProvider
    {
        return $this->findOne(
            callback: fn($q) => $q->where('id', $id)->where('status', ServiceProvider::STATUS_APPROVED),
            relations: ['categories:id,name,slug,icon', 'city:id,name', 'user:id,first_name,last_name,phone_number'],
        );
    }

    /** Admin review queue, optional status filter. */
    public function paginatedForAdmin(?string $status = null, ?int $limit = null)
    {
        return $this->list(
            callback: function ($q) use ($status) {
                if ($status) {
                    $q->where('status', $status);
                }
                $q->latest();
            },
            relations: ['categories:id,name,slug', 'city:id,name', 'user:id,first_name,last_name,phone_number'],
        );
    }
}
