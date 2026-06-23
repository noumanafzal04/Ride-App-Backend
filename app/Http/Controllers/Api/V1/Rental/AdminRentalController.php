<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Actions\Rental\RentalCarAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Rental\RentalCarResource;
use Illuminate\Http\Request;

class AdminRentalController extends Controller
{
    public function __construct(protected RentalCarAction $action) {}

    public function index(Request $request)
    {
        $items = $this->action->adminList($request->query('status'), $request->query('type'), (int) $request->query('per_page', 15));
        return RentalCarResource::collection($items)->wrapWith('rentals')->message('Rental cars.');
    }

    public function show(int $id)
    {
        return (new RentalCarResource($this->action->adminFind($id)))->wrapWith('rental')->message('Rental detail.');
    }

    public function setStatus(Request $request, int $id)
    {
        $data = $request->validate(['status' => ['required', 'in:active,rejected,paused,inactive,pending']]);
        return (new RentalCarResource($this->action->setStatus($id, $data['status'])))->wrapWith('rental')->message('Status updated.');
    }

    public function setPrice(Request $request, int $id)
    {
        $data = $request->validate(['price' => ['required', 'numeric', 'min:0', 'max:9999999']]);
        return (new RentalCarResource($this->action->setPrice($id, (float) $data['price'])))->wrapWith('rental')->message('Price updated.');
    }

    public function setFeatured(Request $request, int $id)
    {
        $data = $request->validate(['is_featured' => ['required', 'boolean']]);
        return (new RentalCarResource($this->action->setFeatured($id, (bool) $data['is_featured'])))->wrapWith('rental')->message('Updated.');
    }
}
