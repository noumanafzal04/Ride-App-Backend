<?php

namespace App\Http\Resources\Api\V1\Vehicle;

use App\Http\Resources\Api\V1\ApiResource;

class VehicleMakeResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'status' => $this->status,
        ];
    }
}
