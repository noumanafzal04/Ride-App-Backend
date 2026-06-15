<?php
// app/Actions/Vehicle/VehicleMakeModelAction.php

namespace App\Actions\Vehicle;

use App\Actions\BaseAction\BaseAction;
use App\Constants\ResourceFields;
use App\Enums\Status;
use App\Repositories\Vehicle\VehicleMakeRepository;
use App\Repositories\Vehicle\VehicleModelRepository;

class VehicleMakeModelAction extends BaseAction
{
    public function __construct(
        VehicleMakeRepository $repository,
        protected VehicleModelRepository $modelRepository,
    ) {
        parent::__construct($repository, 'vehicle_make_model');
    }

    public function makes(array $filters)
    {
        return $this->repository->list(
            callback: function ($query) {
                $query->where('status', Status::ACTIVE->value);
            },
            select: ResourceFields::VEHICLE_MAKE_LIST_FIELDS,
        );
    }

    public function models(array $filters)
    {
        return $this->modelRepository->list(
            callback: function ($query) use ($filters) {

                $query->where('status', Status::ACTIVE->value);

                if (!empty($filters['make_id'])) {
                    $query->where('make_id', $filters['make_id']);
                }
            },
            select: ResourceFields::VEHICLE_MODEL_LIST_FIELDS,
        );
    }
}
