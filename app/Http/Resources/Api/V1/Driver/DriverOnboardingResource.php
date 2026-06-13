<?php

namespace App\Http\Resources\Api\V1\Driver;

use App\Http\Resources\Api\V1\ApiResource;
use App\Http\Resources\Api\V1\Profile\UserProfileResource;
use App\Http\Resources\Api\V1\Vehicle\VehicleResource;

class DriverOnboardingResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone_number,
            'user_type'  => $this->user_type,
            'status'     => $this->status,

            'profile'        => new UserProfileResource($this->whenLoaded('profile')),
            'driver_profile' => new DriverProfileResource($this->whenLoaded('driverProfile')),

            // For single vehicle (most common in onboarding)
            'vehicle' => $this->whenLoaded(
                'vehicles',
                fn() =>
                $this->vehicles->count() === 1
                    ? new VehicleResource($this->vehicles->first())
                    : VehicleResource::collection($this->vehicles)
            ),
        ];
    }
}
