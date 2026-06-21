<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Actions\Service\ServiceBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Service\RateServiceBookingRequest;
use App\Http\Requests\Api\V1\Service\StoreServiceBookingRequest;
use App\Http\Resources\Api\V1\Service\ServiceBookingResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ServiceBookingController extends Controller
{
    public function __construct(protected ServiceBookingAction $action) {}

    // ── Customer ──
    public function store(StoreServiceBookingRequest $request, int $providerId)
    {
        $booking = $this->action->request(auth()->id(), $providerId, $request->validated());

        return (new ServiceBookingResource($booking))
            ->message('Service request sent.')
            ->status(201);
    }

    public function index()
    {
        return ServiceBookingResource::collection($this->action->listForCustomer(auth()->id()))
            ->wrapWith('bookings')
            ->message('Your service requests.');
    }

    public function cancel(int $id)
    {
        return (new ServiceBookingResource($this->action->cancel(auth()->id(), $id)))
            ->message('Request cancelled.');
    }

    public function rate(RateServiceBookingRequest $request, int $id)
    {
        $this->action->rate(auth()->id(), $id, $request->validated());

        return ApiResponse::noContent('Thanks for your review.');
    }

    // ── Provider ──
    public function providerIndex(Request $request)
    {
        return ServiceBookingResource::collection($this->action->providerBookings(auth()->id(), $request->query('status')))
            ->wrapWith('bookings')
            ->message('Service requests.');
    }

    public function accept(Request $request, int $id)
    {
        $price = $request->input('price');
        $booking = $this->action->accept(auth()->id(), $id, $price !== null ? (float) $price : null);

        return (new ServiceBookingResource($booking))->message('Request accepted.');
    }

    public function reject(int $id)
    {
        return (new ServiceBookingResource($this->action->reject(auth()->id(), $id)))->message('Request declined.');
    }

    public function start(int $id)
    {
        return (new ServiceBookingResource($this->action->start(auth()->id(), $id)))->message('Service started.');
    }

    public function complete(int $id)
    {
        return (new ServiceBookingResource($this->action->complete(auth()->id(), $id)))->message('Service completed.');
    }
}
