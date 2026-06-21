<?php

namespace App\Http\Requests\Api\V1\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'   => ['nullable', 'integer', 'exists:service_categories,id'],
            'scheduled_at'  => ['nullable', 'date'],
            'location_type' => ['nullable', Rule::in(['at_shop', 'at_home'])],
            'address'       => ['nullable', 'string', 'max:1000'],
            'car_info'      => ['nullable', 'string', 'max:255'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ];
    }
}
