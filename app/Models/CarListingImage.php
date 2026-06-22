<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarListingImage extends Model
{
    protected $fillable = ['car_listing_id', 'path', 'sort', 'is_primary'];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(CarListing::class, 'car_listing_id');
    }
}
