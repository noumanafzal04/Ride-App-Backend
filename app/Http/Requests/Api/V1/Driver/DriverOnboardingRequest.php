<?php
// app/Http/Requests/Api/V1/Driver/DriverOnboardingRequest.php

namespace App\Http\Requests\Api\V1\Driver;

use App\Models\DriverProfile;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Rules\ModelExistsWithConditions;
use App\Rules\UniqueFieldsValidator;
use Illuminate\Foundation\Http\FormRequest;

class DriverOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $driverProfile = auth()->user()->driverProfile;
        $vehicle       = auth()->user()->vehicles?->first();

        // first time = required, already onboarded = sometimes (partial update)
        $required = $driverProfile ? 'sometimes' : 'required';

        return [

            // ── profile ──────────────────────────────────────────
            'profile.profile_image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'profile.dob'            => ['nullable', 'date'],
            'profile.gender'         => ['nullable', 'string', 'in:male,female,other'],
            'profile.city'           => ['nullable', 'string', 'max:100'],
            'profile.address'        => ['nullable', 'string'],
            'profile.bio'            => ['nullable', 'string', 'max:500'],

            // ── driver credentials ──────────────────────────────
            'driver.cnic_number'         => [$required, 'string', 'max:20'],
            'driver.cnic_front_image'    => [$required, 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'driver.cnic_back_image'     => [$required, 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'driver.license_number'      => [$required, 'string', 'max:50'],
            'driver.license_front_image' => [$required, 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'driver.license_back_image'  => [$required, 'image', 'mimes:jpg,jpeg,png', 'max:5120'],

            // ── vehicle ──────────────────────────────────────────
            'vehicle.model_id' => [
                $required,
                'integer',
                new ModelExistsWithConditions(VehicleModel::class),
            ],
            'vehicle.vehicle_image'       => [$required, 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'vehicle.manufacture_year'    => [$required, 'integer', 'min:2000', 'max:' . date('Y')],
            'vehicle.color'               => [$required, 'string', 'max:40'],
            'vehicle.registration_number' => [$required, 'string', 'max:30'],
            'vehicle.luggage_capacity'    => ['nullable', 'integer', 'min:0'],
            'vehicle.has_air_conditioner' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $driverId  = auth()->user()->driverProfile?->id;
            $vehicleId = auth()->user()->vehicles?->first()?->id;

            UniqueFieldsValidator::validate(
                $validator,
                DriverProfile::class,
                [
                    'driver.cnic_number'    => $this->input('driver.cnic_number'),
                    'driver.license_number' => $this->input('driver.license_number'),
                ],
                ignoreId: $driverId
            );

            UniqueFieldsValidator::validate(
                $validator,
                Vehicle::class,
                [
                    'vehicle.registration_number' => $this->input('vehicle.registration_number'),
                ],
                ignoreId: $vehicleId
            );
        });
    }
}
