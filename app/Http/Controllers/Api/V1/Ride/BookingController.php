<?php

namespace App\Http\Controllers\Api\V1\Ride;

use App\Actions\Ride\BookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ride\BookSeatRequest;
use App\Http\Requests\Api\V1\Ride\RateBookingRequest;
use App\Http\Resources\Api\V1\Ride\RideBookingResource;
use App\Http\Resources\Api\V1\Ride\RatingResource;
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

    // Rider: their own bookings (?status=pending|accepted|rejected|cancelled)
    public function riderIndex(Request $request)
    {
        $bookings = $this->action->riderBookings(auth()->id(), $request->all());

        return RideBookingResource::collection($bookings)
            ->wrapWith('bookings')
            ->message(__("{$this->resourceName}.all"));
    }

    // Rider: cancel their own booking
    public function cancel(int $bookingId)
    {
        $booking = $this->action->cancel(auth()->id(), $bookingId);

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.cancelled"));
    }

    // Either party: mark the booking completed (after departure)
    public function complete(int $bookingId)
    {
        $booking = $this->action->complete(auth()->id(), $bookingId);

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.completed"));
    }

    // Either party: leave an optional review after completion
    public function rate(RateBookingRequest $request, int $bookingId)
    {
        $rating = $this->action->rate(auth()->id(), $bookingId, $request->validated());

        return (new RatingResource($rating))
            ->message(__("{$this->resourceName}.reviewed"))
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
