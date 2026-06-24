<?php

namespace App\Http\Resources\Api\V1\Rental;

use App\Http\Resources\Api\V1\ApiResource;

class RentalBookingResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'status'          => $this->status,
            'start_date'      => $this->start_date?->toDateString(),
            'end_date'        => $this->end_date?->toDateString(),
            'days'            => $this->days,
            'with_driver'     => (bool) $this->with_driver,
            'pickup_location' => $this->pickup_location,
            'total_amount'    => $this->total_amount !== null ? (float) $this->total_amount : null,
            'deposit'         => $this->deposit !== null ? (float) $this->deposit : null,
            'notes'           => $this->notes,
            'rental_car'      => $this->whenLoaded('rentalCar', fn() => $this->rentalCar ? [
                'id'    => $this->rentalCar->id,
                'title' => trim("{$this->rentalCar->make} {$this->rentalCar->model}") . ($this->rentalCar->year ? " {$this->rentalCar->year}" : ''),
                'city'  => $this->rentalCar->city?->name,
            ] : null),
            'customer'        => $this->whenLoaded('customer', fn() => $this->customer ? [
                'id' => $this->customer->id, 'name' => trim("{$this->customer->first_name} {$this->customer->last_name}") ?: 'Customer', 'phone' => $this->customer->phone_number,
            ] : null),
            'owner'           => $this->whenLoaded('owner', fn() => $this->owner ? [
                'id' => $this->owner->id, 'name' => trim("{$this->owner->first_name} {$this->owner->last_name}") ?: 'Owner', 'phone' => $this->owner->phone_number,
            ] : null),
            'is_rated'        => $this->relationLoaded('ratings')
                ? $this->ratings->where('from_user_id', $request->user()?->id)->isNotEmpty()
                : false,
            'can_rate'        => $this->status === \App\Models\RentalBooking::STATUS_COMPLETED
                && ($this->relationLoaded('ratings') ? $this->ratings->where('from_user_id', $request->user()?->id)->isEmpty() : true),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
