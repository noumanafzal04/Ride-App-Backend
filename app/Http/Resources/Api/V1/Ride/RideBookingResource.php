<?php

namespace App\Http\Resources\Api\V1\Ride;

use App\Http\Resources\Api\V1\ApiResource;

class RideBookingResource extends ApiResource
{
    public function toArray($request): array
    {
        $myReview = $this->whenLoaded('ratings', function () {
            $mine = $this->ratings->firstWhere('from_user_id', auth()->id());
            return $mine ? ['rating' => $mine->rating, 'review' => $mine->review] : null;
        }, null);

        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'seats_booked'   => $this->seats_booked,
            'price_per_seat' => $this->price_per_seat,
            'total_amount'   => $this->total_amount,
            'note'           => $this->note,
            'created_at'     => $this->created_at?->toISOString(),

            'is_completed'   => $this->status === 'completed',
            'my_review'      => $myReview,
            'can_review'     => $this->status === 'completed' && empty($myReview),

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
                'price_per_seat' => $this->ridePost?->price_per_seat,
                'from_city'    => $this->ridePost?->relationLoaded('fromCity') ? $this->ridePost->fromCity?->name : null,
                'to_city'      => $this->ridePost?->relationLoaded('toCity') ? $this->ridePost->toCity?->name : null,
                'driver'       => $this->ridePost?->relationLoaded('driver') ? [
                    'id'           => $this->ridePost->driver?->id,
                    'first_name'   => $this->ridePost->driver?->first_name,
                    'last_name'    => $this->ridePost->driver?->last_name,
                    'phone_number' => $this->ridePost->driver?->phone_number,
                ] : null,
            ]),
        ];
    }
}
