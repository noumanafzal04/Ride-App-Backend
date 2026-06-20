<?php

namespace App\Http\Requests\Api\V1\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class AssignInspectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by the `admin` middleware on the route
    }

    public function rules(): array
    {
        return [
            'inspector_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
