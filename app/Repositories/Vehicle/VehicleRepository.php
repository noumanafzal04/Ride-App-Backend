<?php

namespace App\Repositories\Vehicle;

use App\Models\Vehicle;
use App\Repositories\BaseRepository;
use App\Traits\PreparesDBPayload;
use App\Constants\ResourceFields;
use App\Models\VehicleModel;

class VehicleRepository extends BaseRepository
{
    use PreparesDBPayload;

    public function __construct()
    {
        $this->model = new Vehicle();
    }

    public function createForUser(int $userId, array $data): Vehicle
    {
        $model = VehicleModel::findOrFail($data['model_id']);
        $payload = $this->preparePayload($data, ResourceFields::VEHICLE_CREATE_FIELDS, [
            'user_id' => $userId,
            'seating_capacity' => $model->seating_capacity,
        ]);

        return $this->model->create($payload);
    }
}
