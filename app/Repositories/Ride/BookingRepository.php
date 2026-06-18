<?php

namespace App\Repositories\Ride;

use App\Models\RideBooking;
use App\Repositories\BaseRepository;

class BookingRepository extends BaseRepository  // ← must match filename
{
    public function __construct()
    {
        $this->model = new RideBooking();
    }

    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
