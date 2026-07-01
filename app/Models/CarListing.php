<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarListing extends Model
{
    public const TYPE_SELF    = 'self';
    public const TYPE_MANAGED = 'managed';

    public const STATUS_DRAFT    = 'draft';
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_SOLD     = 'sold';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'user_id', 'listing_type', 'status',
        'make', 'model', 'variant', 'year', 'price', 'mileage', 'condition',
        'transmission', 'fuel_type', 'engine_cc', 'color',
        'city_id', 'area', 'description', 'features',
        'inspection_request_id', 'is_featured', 'featured_until', 'views_count', 'sold_at',
    ];

    protected $casts = [
        'features'       => 'array',
        'is_featured'    => 'boolean',
        'featured_until' => 'datetime',
        'price'          => 'decimal:2',
        'sold_at'        => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CarListingImage::class)->orderBy('sort');
    }

    public function inspectionRequest(): BelongsTo
    {
        return $this->belongsTo(InspectionRequest::class, 'inspection_request_id');
    }
}
