<?php

namespace App\Repositories\Ride;

use App\Models\RideBooking;
use App\Repositories\BaseRepository;

class BookingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new RideBooking();
    }
}
