<?php

namespace App\Http\Controllers\Api\V1\Ride;

use App\Actions\Ride\RideAlertAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ride\StoreRideAlertRequest;
use App\Http\Resources\Api\V1\Ride\RideAlertResource;
use App\Support\ApiResponse;

class RideAlertController extends Controller
{
    public $resourceName = 'ride_alert';

    public function __construct(protected RideAlertAction $action) {}

    public function index()
    {
        $items = $this->action->listForUser(auth()->id());

        return RideAlertResource::collection($items)
            ->wrapWith('alerts')
            ->message(__("{$this->resourceName}.all"));
    }

    public function store(StoreRideAlertRequest $request)
    {
        $alert = $this->action->createForUser(auth()->id(), $request->validated());
        $alert->load(['fromCity:id,name', 'toCity:id,name']);

        return (new RideAlertResource($alert))
            ->message(__("{$this->resourceName}.created"))
            ->status(201);
    }

    public function destroy(int $id)
    {
        $this->action->deleteForUser(auth()->id(), $id);

        return ApiResponse::noContent(__("{$this->resourceName}.deleted"));
    }
}
