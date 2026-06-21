<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceProvider extends Model
{
    protected $table = 'service_providers';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_SUSPENDED,
    ];

    protected $fillable = [
        'user_id',
        'business_name',
        'city_id',
        'area',
        'phone',
        'description',
        'status',
        'rating_avg',
        'total_jobs',
    ];

    protected $casts = [
        'rating_avg' => 'decimal:2',
        'total_jobs' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceCategory::class,
            'service_provider_categories',
            'service_provider_id',
            'category_id',
        )->withTimestamps();
    }
}
