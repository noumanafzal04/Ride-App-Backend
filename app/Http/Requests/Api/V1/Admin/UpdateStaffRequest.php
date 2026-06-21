<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'name'     => ['sometimes', 'string', 'max:100'],
            'email'    => ['sometimes', 'email', 'max:255', Rule::unique('admin_users', 'email')->ignore($id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id'  => ['sometimes', 'integer', 'exists:roles,id'],
            'status'   => ['sometimes', Rule::in(['active', 'inactive'])],
        ];
    }
}
