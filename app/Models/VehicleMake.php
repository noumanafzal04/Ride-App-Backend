<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleMake extends Model
{
    protected $table = 'vehicle_makes';

    protected $fillable = [
        'name',
        'status',
    ];

    protected $casts = [
        'status' => Status::class,
    ];

    public function models(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'make_id');
    }
}
