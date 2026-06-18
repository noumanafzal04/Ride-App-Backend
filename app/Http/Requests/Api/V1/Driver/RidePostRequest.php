<?php

namespace App\Http\Requests\Api\V1\Driver;

use App\Models\City;
use App\Models\RidePost;
use App\Rules\ModelExistsWithConditions;
use Illuminate\Foundation\Http\FormRequest;

class RidePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required  = $this->isMethod('post') ? 'required' : 'sometimes';
        $isShared  = $this->input('post_type') === 'shared';

        return [

            'from_city_id' => [
                $required,
                'integer',
                new ModelExistsWithConditions(
                    modelClass: City::class,
                    conditions: [],
                    message: 'Selected departure city does not exist.'
                ),
            ],

            'to_city_id' => [
                $required,
                'integer',
                new ModelExistsWithConditions(
                    modelClass: City::class,
                    conditions: [],
                    message: 'Selected destination city does not exist.'
                ),
            ],

            'from_address'   => ['nullable', 'string', 'max:500'],
            'to_address'     => ['nullable', 'string', 'max:500'],

            'from_latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'from_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'to_latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'to_longitude'   => ['nullable', 'numeric', 'between:-180,180'],

            'departure_at'    => [$required, 'date', 'after:now'],
            'price_per_seat'  => [$required, 'numeric', 'min:0'],
            'luggage_allowed' => ['nullable', 'boolean'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'post_type'       => [$required, 'string', 'in:private,shared'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            // same city guard
            $from = $this->input('from_city_id');
            $to   = $this->input('to_city_id');

            if ($from && $to && (int) $from === (int) $to) {
                $validator->errors()->add(
                    'to_city_id',
                    'Departure and destination city cannot be the same.'
                );
            }

            // one active post per driver guard
            if ($this->isMethod('post')) {
                $exists = RidePost::where('driver_id', auth()->id())
                    ->whereIn('status', ['active', 'full'])
                    ->exists();

                if ($exists) {
                    $validator->errors()->add(
                        'driver_id',
                        'You already have an active ride post. Complete or cancel it before creating a new one.'
                    );
                }
            }
        });
    }
}
