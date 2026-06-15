<?php

namespace App\Repositories\Vehicle;

use App\Models\Vehicle;
use App\Repositories\BaseRepository;
use App\Traits\PreparesDBPayload;
use App\Constants\ResourceFields;

class VehicleRepository extends BaseRepository
{
    use PreparesDBPayload;

    public function __construct()
    {
        $this->model = new Vehicle();
    }

    public function createForUser(int $userId, array $data): Vehicle
    {
        $payload = $this->preparePayload($data, ResourceFields::VEHICLE_CREATE_FIELDS, [
            'user_id' => $userId,
        ]);

        return $this->model->create($payload);
    }
}
