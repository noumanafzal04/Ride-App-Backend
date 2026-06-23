<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalCarImage extends Model
{
    protected $fillable = ['rental_car_id', 'path', 'sort', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];

    public function rentalCar(): BelongsTo { return $this->belongsTo(RentalCar::class); }
}
