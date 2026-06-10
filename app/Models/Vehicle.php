<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'vehicle_type',
        'make',
        'model',
        'manufacturing_year',
        'color',
        'registration_number',
        'seating_capacity',
        'luggage_capacity',
        'vehicle_front_image',
        'vehicle_back_image',
        'vehicle_side_image',
        'air_conditioned',
        'wifi_available',
        'status',
    ];

    protected $casts = [
        'air_conditioned'         => 'boolean',
        'wifi_available'          => 'boolean',
        'status'                  => Status::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
