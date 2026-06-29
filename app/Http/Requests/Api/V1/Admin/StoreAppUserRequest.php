<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['nullable', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'email'        => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:6', 'max:100'],
            'user_type'    => ['required', 'in:user,driver'],
            'city_id'      => ['nullable', 'integer', 'exists:cities,id'],
            'verified'     => ['nullable', 'boolean'],
        ];
    }
}
