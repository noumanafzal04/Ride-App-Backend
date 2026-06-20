<?php

namespace App\Http\Resources\Api\V1\Driver;

use App\Http\Resources\Api\V1\ApiResource;

class DriverTripResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'from_city'    => $this->fromCity?->name,
            'to_city'      => $this->toCity?->name,
            'departure_at' => $this->departure_at?->toISOString(),
            'post_type'    => $this->post_type,
        ];
    }
}
