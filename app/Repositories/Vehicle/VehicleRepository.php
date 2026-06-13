<?php

namespace App\Repositories\Vehicle;

use App\Models\Vehicle;
use App\Repositories\BaseRepository;

class VehicleRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Vehicle();
    }

    public function createForUser(int $userId, array $data): Vehicle
    {
        return $this->model->create([
            'user_id'             => $userId,
            'model_id'            => $data['model_id'],
            'vehicle_image_path'  => $data['vehicle_image_path'],
            'manufacture_year'    => $data['manufacture_year'],
            'color'               => $data['color'],
            'registration_number' => $data['registration_number'],
            'seating_capacity'    => $data['seating_capacity'],
            'luggage_capacity'    => $data['luggage_capacity'] ?? null,
            'has_air_conditioner' => $data['has_air_conditioner'] ?? false,
        ]);
    }

    public function findByUserId(int $userId): ?Vehicle
    {
        return $this->model->where('user_id', $userId)->first();
    }
}
