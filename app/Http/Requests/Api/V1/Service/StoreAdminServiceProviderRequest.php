<?php

namespace App\Http\Requests\Api\V1\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminServiceProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'    => ['nullable', 'string', 'max:100'],
            'phone_number'  => ['required', 'string', 'max:20'],
            'business_name' => ['required', 'string', 'max:150'],
            'city_id'       => ['nullable', 'integer', 'exists:cities,id'],
            'area'          => ['nullable', 'string', 'max:150'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'category_ids'  => ['required', 'array', 'min:1'],
            'category_ids.*'=> ['integer', 'exists:service_categories,id'],
        ];
    }
}
