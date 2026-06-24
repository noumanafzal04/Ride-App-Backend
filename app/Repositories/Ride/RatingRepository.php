<?php

namespace App\Repositories\Ride;

use App\Models\Rating;
use App\Repositories\BaseRepository;

class RatingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Rating();
    }

    // Average rating a user has received (optionally per module type)
    public function avgForUser(int $userId, ?string $type = null): float
    {
        $query = $this->model->where('to_user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        return round((float) $query->avg('rating'), 2);
    }

    // Reviews a driver received from passengers, paginated (latest first).
    public function paginatedReceivedForDriver(int $driverId)
    {
        return $this->paginatedList(
            callback: fn($q) => $q
                ->where('to_user_id', $driverId)
                ->where('rated_as', 'driver')
                ->latest(),
            relations: ['fromUser:id,first_name,last_name'],
        );
    }

    // How many reviews a driver has received.
    public function countForDriver(int $driverId): int
    {
        return $this->model->newQuery()
            ->where('to_user_id', $driverId)
            ->where('rated_as', 'driver')
            ->count();
    }

    // Reviews a service provider received from customers, paginated (latest first).
    public function paginatedReceivedForProvider(int $providerUserId)
    {
        return $this->paginatedList(
            callback: fn($q) => $q
                ->where('to_user_id', $providerUserId)
                ->where('rated_as', 'provider')
                ->latest(),
            relations: ['fromUser:id,first_name,last_name'],
        );
    }
}
