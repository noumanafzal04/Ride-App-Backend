<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Actions\Rental\RentalCarAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreRentalCarRequest;
use App\Http\Resources\Api\V1\Rental\RentalCarResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class RentalCarController extends Controller
{
    public function __construct(protected RentalCarAction $action) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'q', 'category', 'city_id', 'rental_type', 'transmission',
            'price_min', 'price_max', 'rating_min', 'make', 'model', 'sort',
        ]);
        $items = $this->action->browse(
            array_filter($filters, fn($v) => $v !== null && $v !== ''),
            $request->filled('near_lat') ? (float) $request->query('near_lat') : null,
            $request->filled('near_lng') ? (float) $request->query('near_lng') : null,
        );
        return RentalCarResource::collection($items)->wrapWith('rentals')->message('Rental cars.');
    }

    /** Distinct make/model list for the "specific model" filter. */
    public function models()
    {
        return ApiResponse::success(['models' => $this->action->models()], 'Available models.');
    }

    public function mine()
    {
        return RentalCarResource::collection($this->action->mine(auth()->id()))->wrapWith('rentals')->message('Your rentals.');
    }

    public function show(int $id)
    {
        return (new RentalCarResource($this->action->show($id)))->wrapWith('rental')->message('Rental detail.');
    }

    public function store(StoreRentalCarRequest $request)
    {
        $car = $this->action->create(auth()->id(), $request->validated(), $request->file('images') ?? []);
        return (new RentalCarResource($car))->wrapWith('rental')->message('Rental listed.')->status(201);
    }

    public function update(StoreRentalCarRequest $request, int $id)
    {
        $car = $this->action->update(auth()->id(), $id, $request->validated());
        return (new RentalCarResource($car))->wrapWith('rental')->message('Rental updated.');
    }

    public function setStatus(Request $request, int $id)
    {
        $data = $request->validate(['status' => ['required', 'in:active,paused']]);
        $car = $this->action->setOwnerStatus(auth()->id(), $id, $data['status']);
        return (new RentalCarResource($car))->wrapWith('rental')->message('Rental updated.');
    }

    public function destroy(int $id)
    {
        $this->action->destroy(auth()->id(), $id);
        return ApiResponse::noContent('Rental removed.');
    }
}
