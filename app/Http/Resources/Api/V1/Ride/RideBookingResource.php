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

        // The ride is considered "done" once its departure time has passed.
        $departed = $this->relationLoaded('ridePost') && $this->ridePost?->departure_at
            ? $this->ridePost->departure_at->isPast()
            : false;
        // Reviewable after the ride actually happened: completed, OR an accepted
        // booking whose departure has passed. A pre-departure cancel never qualifies.
        $reviewable = ($this->status === 'completed' || ($this->status === 'accepted' && $departed)) && empty($myReview);
        // Can only cancel an accepted seat BEFORE departure.
        $cancellable = in_array($this->status, ['pending', 'accepted'], true) && !$departed;

        // Distance (km) from the ride's origin to the rider's pickup point —
        // shown on the driver's offer card. Haversine, null if coords missing.
        $pickupDistanceKm = null;
        if ($this->pickup_lat !== null && $this->pickup_lng !== null
            && $this->relationLoaded('ridePost')
            && $this->ridePost?->from_latitude !== null && $this->ridePost?->from_longitude !== null) {
            $lat1 = deg2rad((float) $this->ridePost->from_latitude);
            $lng1 = deg2rad((float) $this->ridePost->from_longitude);
            $lat2 = deg2rad((float) $this->pickup_lat);
            $lng2 = deg2rad((float) $this->pickup_lng);
            $a = sin(($lat2 - $lat1) / 2) ** 2 + cos($lat1) * cos($lat2) * sin(($lng2 - $lng1) / 2) ** 2;
            $pickupDistanceKm = round(6371 * 2 * asin(min(1, sqrt($a))), 1);
        }

        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'seats_booked'   => $this->seats_booked,
            'price_per_seat' => $this->price_per_seat,
            'total_amount'   => $this->total_amount,
            'note'           => $this->note,
            'pickup_lat'     => $this->pickup_lat !== null ? (float) $this->pickup_lat : null,
            'pickup_lng'     => $this->pickup_lng !== null ? (float) $this->pickup_lng : null,
            'pickup_distance_km' => $pickupDistanceKm,
            'created_at'     => $this->created_at?->toISOString(),

            'is_completed'   => $this->status === 'completed',
            'departed'       => $departed,
            'my_review'      => $myReview,
            'can_review'     => $reviewable,
            'can_cancel'     => $cancellable,

            'passenger' => $this->whenLoaded('passenger', fn() => [
                'id'            => $this->passenger?->id,
                'first_name'    => $this->passenger?->first_name,
                'last_name'     => $this->passenger?->last_name,
                'phone_number'  => $this->passenger?->phone_number,
                'profile_image' => $this->passenger?->relationLoaded('profile') && $this->passenger->profile?->profile_image
                    ? asset('storage/' . $this->passenger->profile->profile_image)
                    : null,
            ]),

            'ride' => $this->whenLoaded('ridePost', fn() => [
                'id'           => $this->ridePost?->id,
                'post_type'    => $this->ridePost?->post_type,
                'status'       => $this->ridePost?->status,
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
