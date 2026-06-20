<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionRequest extends Model
{
    protected $table = 'inspection_requests';

    // Workflow statuses.
    public const STATUS_PENDING     = 'pending';
    public const STATUS_REVIEWING   = 'reviewing';
    public const STATUS_SCHEDULED   = 'scheduled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_CANCELLED   = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_REVIEWING,
        self::STATUS_SCHEDULED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'user_id',
        'tracking_token',
        'name',
        'phone',
        'email',
        'car_make',
        'car_model',
        'car_year',
        'variant',
        'registration_no',
        'city_id',
        'address',
        'preferred_at',
        'notes',
        'status',
        'assigned_to',
        'scheduled_at',
        'overall_grade',
        'overall_score',
        'inspector_comments',
        'admin_notes',
        'completed_at',
    ];

    protected $casts = [
        'car_year'      => 'integer',
        'preferred_at'  => 'datetime',
        'scheduled_at'  => 'datetime',
        'completed_at'  => 'datetime',
        'overall_score' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function categoryResults(): HasMany
    {
        return $this->hasMany(InspectionCategoryResult::class, 'inspection_request_id');
    }
}
