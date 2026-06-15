<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    protected $table = 'vehicle_models';

    protected $fillable = [
        'make_id',
        'name',
        'seating_capacity',
        'status',
    ];

    protected $casts = [
        'seating_capacity' => 'integer',
        'status' => Status::class,
    ];

    public function make(): BelongsTo
    {
        return $this->belongsTo(VehicleMake::class, 'make_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'model_id');
    }
}
