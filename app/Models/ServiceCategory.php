<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceCategory extends Model
{
    protected $table = 'service_categories';

    protected $fillable = ['name', 'slug', 'icon', 'sort', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort'      => 'integer',
    ];

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceProvider::class,
            'service_provider_categories',
            'category_id',
            'service_provider_id',
        );
    }
}
