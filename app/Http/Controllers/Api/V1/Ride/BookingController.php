<?php

namespace App\Http\Controllers\Api\V1\Ride;

use App\Actions\Ride\BookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ride\BookSeatRequest;
use App\Http\Resources\Api\V1\Ride\RideBookingResource;
use App\Models\RideBooking;
use App\Models\RidePost;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public $resourceName = 'ride_booking';

    public function __construct(protected BookingAction $action) {}

    // Rider books seats on a ride post
    public function store(BookSeatRequest $request, RidePost $ridePost)
    {
        $booking = $this->action->book(auth()->id(), $ridePost, $request->validated());

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.created"))
            ->status(201);
    }

    // Driver: bookings received on their posts (?status=pending|accepted|rejected)
    public function driverIndex(Request $request)
    {
        $bookings = $this->action->driverBookings(auth()->id(), $request->all());

        return RideBookingResource::collection($bookings)
            ->wrapWith('bookings')
            ->message(__("{$this->resourceName}.all"));
    }

    // Driver accepts a booking
    public function accept(RideBooking $booking)
    {
        $booking = $this->action->accept(auth()->id(), $booking);

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.accepted"));
    }

    // Driver rejects a booking
    public function reject(RideBooking $booking)
    {
        $booking = $this->action->reject(auth()->id(), $booking);

        return (new RideBookingResource($booking))
            ->message(__("{$this->resourceName}.rejected"));
    }
}
