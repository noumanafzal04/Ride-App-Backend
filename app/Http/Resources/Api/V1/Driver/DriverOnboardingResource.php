<?php
// app/Http/Resources/Api/V1/Driver/DriverOnboardingResource.php

namespace App\Http\Resources\Api\V1\Driver;

use App\Http\Resources\Api\V1\ApiResource;

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

            'profile' => $this->whenLoaded('profile', function () {
                return [
                    'profile_image' => $this->profile?->profile_image,
                    'dob'           => $this->profile?->dob,
                    'gender'        => $this->profile?->gender,
                    'city'          => $this->profile?->city,
                    'address'       => $this->profile?->address,
                    'bio'           => $this->profile?->bio,
                ];
            }),

            'driver_profile' => $this->whenLoaded('driverProfile', function () {
                return [
                    'cnic_number'         => $this->driverProfile?->cnic_number,
                    'license_number'      => $this->driverProfile?->license_number,
                    'verification_status' => $this->driverProfile?->verification_status,
                    'rating_avg'          => $this->driverProfile?->rating_avg,
                    'is_online'           => $this->driverProfile?->is_online,
                ];
            }),

            'vehicle' => $this->whenLoaded('vehicles', function () {
                return $this->vehicles->map(fn($v) => [
                    'id'                  => $v->id,
                    'manufacture_year'    => $v->manufacture_year,
                    'color'               => $v->color,
                    'registration_number' => $v->registration_number,
                    'seating_capacity'    => $v->seating_capacity,
                    'luggage_capacity'    => $v->luggage_capacity,
                    'has_air_conditioner' => $v->has_air_conditioner,
                    'vehicle_image_path'  => $v->vehicle_image_path,
                    'model' => $v->whenLoaded('vehicleModel', fn() => [
                        'id'   => $v->vehicleModel?->id,
                        'name' => $v->vehicleModel?->name,
                        'make' => $v->vehicleModel?->whenLoaded('make', fn() => [
                            'id'   => $v->vehicleModel->make?->id,
                            'name' => $v->vehicleModel->make?->name,
                        ]),
                    ]),
                ]);
            }),
        ];
    }
}
