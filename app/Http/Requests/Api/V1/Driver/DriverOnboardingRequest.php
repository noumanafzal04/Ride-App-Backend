<?php

namespace App\Http\Requests\Api\V1\Driver;

use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Rules\ModelExistsWithConditions;
use Illuminate\Foundation\Http\FormRequest;

class DriverOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // ── profile ─────────────────────────────────────────
            'profile.profile_photo'  => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'profile.dob'  => ['nullable', 'date'],
            'profile.gender'         => ['nullable', 'string', 'in:male,female,other'],
            'profile.city'           => ['nullable', 'string', 'max:100'],
            'profile.address'        => ['nullable', 'string'],

            // ── driver credentials ──────────────────────────────
            'driver.cnic_number'         => ['required', 'string', 'max:20', 'unique:driver_profiles,cnic_number'],
            'driver.cnic_front_image'    => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'driver.cnic_back_image'     => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'driver.license_number'      => ['required', 'string', 'max:50', 'unique:driver_profiles,license_number'],
            'driver.license_front_image' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'driver.license_back_image'  => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],

            // ── vehicle ─────────────────────────────────────────
            'vehicle.model_id' => [
                'required',
                'integer',
                new ModelExistsWithConditions(
                    VehicleModel::class,
                ),
            ],
            'vehicle.vehicle_image'       => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'vehicle.manufacture_year'    => ['required', 'integer', 'min:2000', 'max:' . date('Y')],
            'vehicle.color'               => ['required', 'string', 'max:40'],
            'vehicle.registration_number' => ['required', 'string', 'max:30', 'unique:vehicles,registration_number'],
            'vehicle.seating_capacity'    => ['required', 'integer', 'min:1', 'max:20'],
            'vehicle.luggage_capacity'    => ['nullable', 'integer', 'min:0'],
            'vehicle.has_air_conditioner' => ['nullable', 'boolean'],
        ];
    }
}
