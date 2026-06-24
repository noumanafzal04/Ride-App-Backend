<?php

namespace App\Repositories\Rental;

use App\Models\RentalBooking;
use App\Repositories\BaseRepository;

class RentalBookingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new RentalBooking();
    }

    protected array $relations = [
        'rentalCar:id,make,model,year,city_id', 'rentalCar.city:id,name',
        'customer:id,first_name,last_name,phone_number',
        'owner:id,first_name,last_name,phone_number',
    ];

    public function showWithRelations(int $id): ?RentalBooking
    {
        return $this->findOne(callback: fn($q) => $q->where('id', $id), relations: $this->relations);
    }

    public function paginatedForCustomer(int $customerId)
    {
        return $this->paginatedList(
            callback: fn($q) => $q->where('customer_id', $customerId)->latest(),
            relations: array_merge($this->relations, ['ratings:id,rateable_id,rateable_type,from_user_id']),
        );
    }

    public function paginatedForOwner(int $ownerId, ?string $status = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($ownerId, $status) {
                $q->where('owner_id', $ownerId);
                if ($status) $q->where('status', $status);
                $q->latest();
            },
            relations: $this->relations,
        );
    }
}
