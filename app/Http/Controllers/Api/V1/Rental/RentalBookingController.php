<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Actions\Rental\RentalBookingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rental\StoreRentalBookingRequest;
use App\Http\Resources\Api\V1\Rental\RentalBookingResource;
use Illuminate\Http\Request;

class RentalBookingController extends Controller
{
    public function __construct(protected RentalBookingAction $action) {}

    // Customer books a rental car.
    public function store(StoreRentalBookingRequest $request, int $rentalId)
    {
        $booking = $this->action->request(auth()->id(), $rentalId, $request->validated());
        return (new RentalBookingResource($booking))->wrapWith('booking')->message('Rental requested.')->status(201);
    }

    public function mine()
    {
        return RentalBookingResource::collection($this->action->listForCustomer(auth()->id()))
            ->wrapWith('bookings')->message('Your rental bookings.');
    }

    public function ownerBookings(Request $request)
    {
        return RentalBookingResource::collection($this->action->listForOwner(auth()->id(), $request->query('status')))
            ->wrapWith('bookings')->message('Booking requests.');
    }

    public function cancel(int $id)
    {
        return (new RentalBookingResource($this->action->cancel(auth()->id(), $id)))->wrapWith('booking')->message('Cancelled.');
    }

    // action = accept | reject | start | complete
    public function action(int $id, string $action)
    {
        return (new RentalBookingResource($this->action->ownerAction(auth()->id(), $id, $action)))->wrapWith('booking')->message('Updated.');
    }

    public function rate(Request $request, int $id)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);
        return (new RentalBookingResource($this->action->rate(auth()->id(), $id, $data)))->wrapWith('booking')->message('Thanks for your review.');
    }
}
