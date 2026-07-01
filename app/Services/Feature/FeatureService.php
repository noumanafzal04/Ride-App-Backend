<?php

namespace App\Services\Feature;

use App\Exceptions\ApiException;
use App\Models\FeatureOrder;
use App\Models\FeatureSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * "Feature this listing" — a paid boost. Payment is marked instantly for now
 * (no gateway yet); swap `markPaid()` for a real gateway callback later.
 */
class FeatureService
{
    public const MODULE_BUYSELL = 'buysell';
    public const MODULE_RENTAL  = 'rental';

    /** Price + duration for a module (sensible defaults if unset). */
    public function pricing(string $module): array
    {
        $s = FeatureSetting::where('module', $module)->first();
        return [
            'module'        => $module,
            'price'         => $s ? (float) $s->price : 500.0,
            'duration_days' => $s ? (int) $s->duration_days : 7,
            'is_active'     => $s ? (bool) $s->is_active : true,
        ];
    }

    /**
     * Boost a listing/rental: records a paid order and marks it featured for N days.
     * $model must expose `is_featured` + `featured_until`.
     */
    public function purchase(int $userId, Model $model, string $module): Model
    {
        $p = $this->pricing($module);
        if (!$p['is_active']) {
            throw new ApiException('Featuring is currently unavailable.', 422);
        }

        $until = now()->addDays($p['duration_days']);

        return DB::transaction(function () use ($userId, $model, $module, $p, $until) {
            FeatureOrder::create([
                'user_id'        => $userId,
                'orderable_type' => $model::class,
                'orderable_id'   => $model->id,
                'module'         => $module,
                'days'           => $p['duration_days'],
                'amount'         => $p['price'],
                'status'         => 'paid',
                'paid_at'        => now(),
                'expires_at'     => $until,
            ]);

            // Extend from an existing active feature window if there is one.
            $base = ($model->featured_until && $model->featured_until->isFuture())
                ? $model->featured_until->copy()->addDays($p['duration_days'])
                : $until;

            $model->forceFill(['is_featured' => true, 'featured_until' => $base])->save();

            return $model->fresh();
        });
    }
}
