<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalCar extends Model
{
    public const TYPE_SELF    = 'self';
    public const TYPE_MANAGED = 'managed';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_ACTIVE   = 'active';
    public const STATUS_PAUSED   = 'paused';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'user_id', 'listing_type', 'status', 'make', 'model', 'variant', 'year', 'category', 'seats',
        'transmission', 'fuel_type', 'color', 'rental_type', 'price_per_day', 'price_per_day_self',
        'deposit', 'min_days', 'city_id', 'area', 'description', 'features',
        'inspection_request_id', 'is_featured', 'views_count',
    ];

    protected $casts = [
        'features'           => 'array',
        'is_featured'        => 'boolean',
        'price_per_day'      => 'decimal:2',
        'price_per_day_self' => 'decimal:2',
        'deposit'            => 'decimal:2',
    ];

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function city(): BelongsTo { return $this->belongsTo(City::class); }
    public function images(): HasMany { return $this->hasMany(RentalCarImage::class)->orderBy('sort'); }
    public function inspectionRequest(): BelongsTo { return $this->belongsTo(InspectionRequest::class, 'inspection_request_id'); }
}
