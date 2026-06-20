<?php

namespace App\Http\Resources\Api\V1\Ride;

use App\Http\Resources\Api\V1\ApiResource;

class RideAlertResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'from_city_id' => $this->from_city_id,
            'to_city_id'   => $this->to_city_id,
            'from_city'    => $this->whenLoaded('fromCity', fn() => [
                'id'   => $this->fromCity?->id,
                'name' => $this->fromCity?->name,
            ]),
            'to_city'      => $this->whenLoaded('toCity', fn() => [
                'id'   => $this->toCity?->id,
                'name' => $this->toCity?->name,
            ]),
            'alert_date'   => $this->alert_date?->toDateString(),
            'is_active'    => $this->is_active,
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
