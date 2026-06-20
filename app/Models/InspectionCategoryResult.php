<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionCategoryResult extends Model
{
    protected $table = 'inspection_category_results';

    // Condition → weight used to compute the overall score (na is excluded).
    public const CONDITIONS = ['excellent', 'good', 'fair', 'poor', 'na'];
    public const WEIGHTS = [
        'excellent' => 100,
        'good'      => 75,
        'fair'      => 50,
        'poor'      => 25,
    ];

    protected $fillable = [
        'inspection_request_id',
        'category_id',
        'condition',
        'notes',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(InspectionCategory::class, 'category_id');
    }
}
