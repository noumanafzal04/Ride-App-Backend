<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalCarRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'listing_type'       => ['nullable', 'in:self,managed'],
            'make'               => ['required', 'string', 'max:100'],
            'model'              => ['required', 'string', 'max:100'],
            'variant'            => ['nullable', 'string', 'max:100'],
            'year'               => ['required', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'category'           => ['nullable', 'in:economy,sedan,suv,luxury,van'],
            'seats'              => ['nullable', 'integer', 'min:1', 'max:50'],
            'transmission'       => ['nullable', 'in:automatic,manual'],
            'fuel_type'          => ['nullable', 'in:petrol,diesel,hybrid,electric,cng'],
            'color'              => ['nullable', 'string', 'max:50'],
            'rental_type'        => ['nullable', 'in:with_driver,self_drive,both'],
            'price_per_day'      => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'price_per_day_self' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'deposit'            => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'min_days'           => ['nullable', 'integer', 'min:1', 'max:365'],
            'city_id'            => ['nullable', 'integer', 'exists:cities,id'],
            'area'               => ['nullable', 'string', 'max:150'],
            'description'        => ['nullable', 'string', 'max:5000'],
            'features'           => ['nullable', 'array'],
            'features.*'         => ['string', 'max:100'],
            'inspection_request_id' => ['nullable', 'integer'],
            'images'             => ['nullable', 'array', 'max:12'],
            'images.*'           => ['image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ];
    }
}
