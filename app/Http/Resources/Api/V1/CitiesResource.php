<?php

namespace App\Http\Resources\Api\V1;

use App\Http\Resources\Api\V1\ApiResource;

class CitiesResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lat' => $this->lat,
            'lon' => $this->lon,
//            'status' => $this->status,
        ];
    }
}
