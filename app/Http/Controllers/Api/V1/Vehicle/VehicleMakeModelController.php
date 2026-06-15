<?php
// app/Http/Controllers/Api/V1/Vehicle/VehicleMakeModelController.php

namespace App\Http\Controllers\Api\V1\Vehicle;

use App\Actions\Vehicle\VehicleMakeModelAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Vehicle\VehicleMakeResource;
use App\Http\Resources\Api\V1\Vehicle\VehicleModelResource;
use Illuminate\Http\Request;

class VehicleMakeModelController extends Controller
{
    public $resourceName = 'vehicle_make_model';

    public function __construct(protected VehicleMakeModelAction $action) {}

    public function makes(Request $request)
    {
        $list = $this->action->makes($request->all());

        return VehicleMakeResource::collection($list)
            ->wrapWith('makes')
            ->message(__("{$this->resourceName}.makes_list"));
    }

    public function models(Request $request)
    {
        $filters = $request->validate([
            'make_id' => ['required', 'integer'],
        ]);

        $list = $this->action->models($filters);

        return VehicleModelResource::collection($list)
            ->wrapWith('models')
            ->message(__("{$this->resourceName}.models_list"));
    }
}
