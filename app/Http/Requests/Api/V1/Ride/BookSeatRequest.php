<?php

namespace App\Http\Requests\Api\V1\Ride;

use Illuminate\Foundation\Http\FormRequest;

class BookSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seats' => ['required', 'integer', 'min:1', 'max:20'],
            'note'  => ['nullable', 'string', 'max:500'],
        ];
    }
}
