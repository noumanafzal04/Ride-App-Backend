<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
        'user_id',
        'model_id',              // FK to vehicle_models
        'vehicle_image_path',    // single image column
        'manufacture_year',      // was manufacturing_year
        'color',
        'registration_number',
        'seating_capacity',
        'luggage_capacity',
        'has_air_conditioner',   // was air_conditioned
        'status',
    ];

    protected $casts = [
        'has_air_conditioner' => 'boolean',
        'manufacture_year'    => 'integer',
        'seating_capacity'    => 'integer',
        'luggage_capacity'    => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }
}
