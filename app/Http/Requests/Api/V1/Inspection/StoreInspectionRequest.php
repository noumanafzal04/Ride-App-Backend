<?php

namespace App\Http\Requests\Api\V1\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public: guests + logged-in users
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:255'],

            'car_make'        => ['required', 'string', 'max:100'],
            'car_model'       => ['required', 'string', 'max:100'],
            'car_year'        => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'variant'         => ['nullable', 'string', 'max:100'],
            'registration_no' => ['nullable', 'string', 'max:50'],

            'city_id'         => ['nullable', 'integer', 'exists:cities,id'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'preferred_at'    => ['nullable', 'date'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ];
    }
}
