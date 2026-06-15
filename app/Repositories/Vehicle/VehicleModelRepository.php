<?php
// app/Repositories/Vehicle/VehicleModelRepository.php

namespace App\Repositories\Vehicle;

use App\Models\VehicleModel;
use App\Repositories\BaseRepository;

class VehicleModelRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new VehicleModel();
    }
}
