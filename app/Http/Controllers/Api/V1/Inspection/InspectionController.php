<?php

namespace App\Http\Controllers\Api\V1\Inspection;

use App\Actions\Inspection\InspectionRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Inspection\StoreInspectionRequest;
use App\Http\Resources\Api\V1\Inspection\InspectionRequestResource;

class InspectionController extends Controller
{
    public function __construct(protected InspectionRequestAction $action) {}

    /**
     * Submit an inspection request. Public — guests allowed. If a valid token
     * is present, the request is linked to that user for tracking.
     */
    public function store(StoreInspectionRequest $request)
    {
        $userId = auth('api')->id(); // null for guests
        $created = $this->action->submit($userId, $request->validated());
        $created->load('city:id,name');

        return (new InspectionRequestResource($created))
            ->message('Inspection request submitted. Our team will contact you shortly.')
            ->status(201);
    }

    /**
     * The authenticated requester's own inspection requests.
     */
    public function index()
    {
        $items = $this->action->listForUser(auth()->id());

        return InspectionRequestResource::collection($items)
            ->wrapWith('requests')
            ->message('Your inspection requests.');
    }

    public function show(int $id)
    {
        $item = $this->action->showForUser(auth()->id(), $id);

        return (new InspectionRequestResource($item))
            ->message('Inspection request.');
    }

    public function cancel(int $id)
    {
        $item = $this->action->cancelForUser(auth()->id(), $id);

        return (new InspectionRequestResource($item))
            ->message('Inspection request cancelled.');
    }

    /**
     * Public status lookup by tracking code (guests, no auth).
     */
    public function track(string $token)
    {
        $item = $this->action->trackByToken($token);

        return (new InspectionRequestResource($item))
            ->message('Inspection status.');
    }
}
