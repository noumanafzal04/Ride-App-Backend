<?php

namespace App\Http\Resources\Api\V1\Admin;

use App\Http\Resources\Api\V1\ApiResource;

class AdminRideResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'status'          => $this->status,
            'post_type'       => $this->post_type,
            'from_city'       => $this->whenLoaded('fromCity', fn() => $this->fromCity?->name),
            'to_city'         => $this->whenLoaded('toCity', fn() => $this->toCity?->name),
            'departure_at'    => $this->departure_at?->toISOString(),
            'available_seats' => $this->available_seats,
            'price_per_seat'  => $this->price_per_seat !== null ? (float) $this->price_per_seat : null,
            'bookings_count'  => $this->bookings_count ?? 0,
            'driver'          => $this->whenLoaded('driver', fn() => [
                'id'    => $this->driver?->id,
                'name'  => trim("{$this->driver?->first_name} {$this->driver?->last_name}") ?: 'Driver',
                'phone' => $this->driver?->phone_number,
            ]),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
