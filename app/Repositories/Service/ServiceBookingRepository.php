<?php

namespace App\Repositories\Service;

use App\Models\ServiceBooking;
use App\Repositories\BaseRepository;

class ServiceBookingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ServiceBooking();
    }

    public function paginatedForCustomer(int $customerId, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: fn($q) => $q->where('customer_id', $customerId)->latest(),
            relations: [
                'category:id,name,slug,icon',
                'provider:id,business_name,phone,user_id',
            ],
            limit: $limit,
        );
    }

    public function paginatedForProvider(int $providerId, ?string $status = null, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($providerId, $status) {
                $q->where('provider_id', $providerId);
                if ($status) {
                    $q->where('status', $status);
                }
                $q->latest();
            },
            relations: [
                'category:id,name,slug,icon',
                'customer:id,first_name,last_name,phone_number',
            ],
            limit: $limit,
        );
    }

    public function showWithRelations(int $id): ServiceBooking
    {
        return $this->model->newQuery()
            ->with([
                'category:id,name,slug,icon',
                'provider:id,business_name,phone,user_id',
                'customer:id,first_name,last_name,phone_number',
            ])
            ->findOrFail($id);
    }
}
