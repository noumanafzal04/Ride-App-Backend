<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Http\Resources\Api\V1\ApiResource;
use App\Http\Resources\Api\V1\Profile\UserProfileResource;
use Illuminate\Http\Request;

class UserResource extends ApiResource
{
    public function toArray(
        Request $request
    ): array {

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'user_type' => $this->user_type,
            'is_admin' => (bool) $this->is_admin,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,

            'profile' => $this->whenLoaded('profile', fn() => new UserProfileResource($this->profile)),

            // Driver-only blocks (only present when eager-loaded for drivers)
            'driver_profile' => $this->whenLoaded('driverProfile', fn() => [
                'cnic_number'         => $this->driverProfile?->cnic_number,
                'license_number'      => $this->driverProfile?->license_number,
                'verification_status' => $this->driverProfile?->verification_status,
                'rating_avg'          => $this->driverProfile?->rating_avg,
                'total_trips'         => $this->driverProfile?->total_trips,
                'is_online'           => $this->driverProfile?->is_online,
            ]),

            'vehicles' => $this->whenLoaded('vehicles', fn() => $this->vehicles->map(fn($v) => [
                'id'                  => $v->id,
                'manufacture_year'    => $v->manufacture_year,
                'color'               => $v->color,
                'registration_number' => $v->registration_number,
                'seating_capacity'    => $v->seating_capacity,
                'luggage_capacity'    => $v->luggage_capacity,
                'has_air_conditioner' => $v->has_air_conditioner,
                'vehicle_image_path'  => $v->vehicle_image_path,
                'model' => $v->relationLoaded('vehicleModel') && $v->vehicleModel ? [
                    'id'   => $v->vehicleModel->id,
                    'name' => $v->vehicleModel->name,
                    'make' => $v->vehicleModel->relationLoaded('make') && $v->vehicleModel->make ? [
                        'id'   => $v->vehicleModel->make->id,
                        'name' => $v->vehicleModel->make->name,
                    ] : null,
                ] : null,
            ])),
        ];
    }
}
