<?php
// app/Repositories/Vehicle/VehicleMakeRepository.php

namespace App\Repositories\Vehicle;

use App\Models\VehicleMake;
use App\Repositories\BaseRepository;

class VehicleMakeRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new VehicleMake();
    }
}
