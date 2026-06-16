<?php
// app/Http/Resources/Api/V1/RidePost/RidePostResource.php

namespace App\Http\Resources\Api\V1\Driver;

use App\Http\Resources\Api\V1\ApiResource;

class RidePostResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'post_type'       => $this->post_type,
            'status'          => $this->status,
            'departure_at'    => $this->departure_at?->toISOString(),
            'available_seats' => $this->available_seats,
            'price_per_seat'  => $this->price_per_seat,
            'luggage_allowed' => $this->luggage_allowed,
            'notes'           => $this->notes,

            'from' => [
                'city'      => $this->whenLoaded('fromCity', fn() => [
                    'id'   => $this->fromCity?->id,
                    'name' => $this->fromCity?->name,
                ]),
                'address'   => $this->from_address,
                'latitude'  => $this->from_latitude,
                'longitude' => $this->from_longitude,
            ],

            'to' => [
                'city'      => $this->whenLoaded('toCity', fn() => [
                    'id'   => $this->toCity?->id,
                    'name' => $this->toCity?->name,
                ]),
                'address'   => $this->to_address,
                'latitude'  => $this->to_latitude,
                'longitude' => $this->to_longitude,
            ],

            'driver' => $this->whenLoaded('driver', fn() => [
                'id'           => $this->driver?->id,
                'first_name'   => $this->driver?->first_name,
                'last_name'    => $this->driver?->last_name,
                'phone_number' => $this->driver?->phone_number,
                'vehicle'      => $this->driver?->vehicles?->first() ? [
                    'id'                 => $this->driver->vehicles->first()->id,
                    'color'              => $this->driver->vehicles->first()->color,
                    'seating_capacity'   => $this->driver->vehicles->first()->seating_capacity,
                    'vehicle_image_path' => $this->driver->vehicles->first()->vehicle_image_path,
                    'model'              => $this->driver->vehicles->first()->relationLoaded('vehicleModel') ? [
                        'name' => $this->driver->vehicles->first()->vehicleModel?->name,
                        'make' => $this->driver->vehicles->first()->vehicleModel?->make?->name,
                    ] : null,
                ] : null,
            ]),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
