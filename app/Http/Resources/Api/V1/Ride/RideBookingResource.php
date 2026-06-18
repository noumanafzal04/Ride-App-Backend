<?php

namespace App\Http\Resources\Api\V1\Ride;

use App\Http\Resources\Api\V1\ApiResource;

class RideBookingResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'seats_booked'   => $this->seats_booked,
            'price_per_seat' => $this->price_per_seat,
            'total_amount'   => $this->total_amount,
            'note'           => $this->note,
            'created_at'     => $this->created_at?->toISOString(),

            'passenger' => $this->whenLoaded('passenger', fn() => [
                'id'           => $this->passenger?->id,
                'first_name'   => $this->passenger?->first_name,
                'last_name'    => $this->passenger?->last_name,
                'phone_number' => $this->passenger?->phone_number,
            ]),

            'ride' => $this->whenLoaded('ridePost', fn() => [
                'id'           => $this->ridePost?->id,
                'post_type'    => $this->ridePost?->post_type,
                'departure_at' => $this->ridePost?->departure_at?->toISOString(),
                'from_city'    => $this->ridePost?->relationLoaded('fromCity') ? $this->ridePost->fromCity?->name : null,
                'to_city'      => $this->ridePost?->relationLoaded('toCity') ? $this->ridePost->toCity?->name : null,
            ]),
        ];
    }
}
