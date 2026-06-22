<?php

namespace App\Http\Requests\Marketplace;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'listing_type'          => ['nullable', 'in:self,managed'],
            'make'                  => ['required', 'string', 'max:100'],
            'model'                 => ['required', 'string', 'max:100'],
            'variant'               => ['nullable', 'string', 'max:100'],
            'year'                  => ['required', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'price'                 => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'mileage'               => ['nullable', 'integer', 'min:0'],
            'condition'             => ['nullable', 'in:new,used'],
            'transmission'          => ['nullable', 'in:automatic,manual'],
            'fuel_type'             => ['nullable', 'in:petrol,diesel,hybrid,electric,cng'],
            'engine_cc'             => ['nullable', 'integer', 'min:0'],
            'color'                 => ['nullable', 'string', 'max:50'],
            'city_id'               => ['nullable', 'integer', 'exists:cities,id'],
            'area'                  => ['nullable', 'string', 'max:150'],
            'description'           => ['nullable', 'string', 'max:5000'],
            'features'              => ['nullable', 'array'],
            'features.*'            => ['string', 'max:100'],
            'inspection_request_id' => ['nullable', 'integer'],
            'images'                => ['nullable', 'array', 'max:12'],
            'images.*'              => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];
    }
}
