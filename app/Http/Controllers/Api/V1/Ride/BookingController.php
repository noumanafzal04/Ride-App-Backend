<?php

namespace App\Http\Controllers\Api\V1\Ride;

use App\Actions\Ride\BookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ride\BookSeatRequest;
use App\Http\Resources\Api\V1\Ride\RideBookingResource;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public $resourceName = 'ride_booking';

    public function __construct(protected BookingAction $action) {}

    public function store(BookSeatRequest $request, int $ridePostId)
    {
        $booking = $this->action->book(auth()->id(), $ridePostId, $request->validated());

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.created"))
            ->status(201);
    }

    public function driverIndex(Request $request)
    {
        $bookings = $this->action->driverBookings(auth()->id(), $request->all());

        return RideBookingResource::collection($bookings)
            ->wrapWith('bookings')
            ->message(__("{$this->resourceName}.all"));
    }

    public function accept(int $bookingId)
    {
        $booking = $this->action->accept(auth()->id(), $bookingId);

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.accepted"));
    }

    public function reject(int $bookingId)
    {
        $booking = $this->action->reject(auth()->id(), $bookingId);

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.rejected"));
    }
}
