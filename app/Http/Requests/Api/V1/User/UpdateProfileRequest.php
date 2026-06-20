<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'           => ['sometimes', 'string', 'max:100'],
            'last_name'            => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile'              => ['sometimes', 'array'],
            'profile.dob'          => ['sometimes', 'nullable', 'date'],
            'profile.gender'       => ['sometimes', 'nullable', 'in:male,female,other'],
            'profile.city'         => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.address'      => ['sometimes', 'nullable', 'string', 'max:500'],
            'profile.bio'          => ['sometimes', 'nullable', 'string', 'max:500'],
            'profile.profile_image' => ['sometimes', 'nullable', 'image', 'max:5120'],
        ];
    }
}
