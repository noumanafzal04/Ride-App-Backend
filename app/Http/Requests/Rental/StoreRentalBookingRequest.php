<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'start_date'      => ['required', 'date'],
            'end_date'        => ['required', 'date', 'after_or_equal:start_date'],
            'with_driver'     => ['nullable', 'boolean'],
            'pickup_location' => ['nullable', 'string', 'max:200'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }
}
