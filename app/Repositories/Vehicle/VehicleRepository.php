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

    // VehicleRepository.php

    public function updateOrCreateForUser(int $userId, array $data): Vehicle
    {
        if (!empty($data['model_id'])) {
            $model = VehicleModel::findOrFail($data['model_id']);
            $data['seating_capacity'] = $model->seating_capacity;
        }

        $payload = $this->preparePayload($data, ResourceFields::VEHICLE_CREATE_FIELDS);

        return $this->model->updateOrCreate(['user_id' => $userId], $payload);
    }
}
