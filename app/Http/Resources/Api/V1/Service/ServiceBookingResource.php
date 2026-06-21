<?php

namespace App\Http\Resources\Api\V1\Service;

use App\Http\Resources\Api\V1\ApiResource;

class ServiceBookingResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'status'        => $this->status,
            'scheduled_at'  => $this->scheduled_at?->toISOString(),
            'location_type' => $this->location_type,
            'address'       => $this->address,
            'car_info'      => $this->car_info,
            'notes'         => $this->notes,
            'price'         => $this->price,
            'completed_at'  => $this->completed_at?->toISOString(),
            'created_at'    => $this->created_at?->toISOString(),

            'category'      => $this->whenLoaded('category', fn() => $this->category ? [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
            ] : null),

            'provider'      => $this->whenLoaded('provider', fn() => $this->provider ? [
                'id'            => $this->provider->id,
                'business_name' => $this->provider->business_name,
                'phone'         => $this->provider->phone,
            ] : null),

            'customer'      => $this->whenLoaded('customer', fn() => $this->customer ? [
                'id'    => $this->customer->id,
                'name'  => trim($this->customer->first_name . ' ' . $this->customer->last_name),
                'phone' => $this->customer->phone_number,
            ] : null),
        ];
    }
}
