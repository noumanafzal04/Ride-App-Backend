<?php

namespace App\Http\Requests\Api\V1\Ride;

use Illuminate\Foundation\Http\FormRequest;

class StoreRideAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_city_id' => ['required', 'integer', 'exists:cities,id', 'different:to_city_id'],
            'to_city_id'   => ['required', 'integer', 'exists:cities,id'],
            'alert_date'   => ['nullable', 'date'],
        ];
    }
}
