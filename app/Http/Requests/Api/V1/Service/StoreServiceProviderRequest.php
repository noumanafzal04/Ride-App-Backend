<?php

namespace App\Http\Requests\Api\V1\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name'  => ['required', 'string', 'max:255'],
            'category_ids'   => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:service_categories,id'],
            'city_id'        => ['nullable', 'integer', 'exists:cities,id'],
            'area'           => ['nullable', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:30'],
            'description'    => ['nullable', 'string', 'max:2000'],
        ];
    }
}
