<?php

namespace App\Http\Requests\Api\V1\Ride;

use Illuminate\Foundation\Http\FormRequest;

class RateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
