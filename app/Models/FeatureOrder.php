<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeatureOrder extends Model
{
    protected $fillable = [
        'user_id', 'orderable_type', 'orderable_id', 'module',
        'days', 'amount', 'status', 'paid_at', 'expires_at',
    ];

    protected $casts = [
        'days'       => 'integer',
        'amount'     => 'decimal:2',
        'paid_at'    => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
