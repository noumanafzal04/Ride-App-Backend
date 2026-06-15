<?php

namespace App\Http\Resources\Api\V1\Vehicle;

use App\Http\Resources\Api\V1\ApiResource;

class VehicleModelResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'make_id'          => $this->make_id,
            'name'             => $this->name,
            'seating_capacity' => $this->seating_capacity,
            'status'           => $this->status,
        ];
    }
}
