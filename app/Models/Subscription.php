<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'billing_subscriptions';

    protected $fillable = [
        'user_id', 'module', 'plan_id', 'posts_allowed', 'posts_used',
        'starts_at', 'ends_at', 'status', 'source', 'price_paid',
    ];

    protected $casts = [
        'posts_allowed' => 'integer',
        'posts_used'    => 'integer',
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'price_paid'    => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUsable(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ends_at?->isFuture()
            && $this->posts_used < $this->posts_allowed;
    }

    public function postsLeft(): int
    {
        return max(0, $this->posts_allowed - $this->posts_used);
    }
}
