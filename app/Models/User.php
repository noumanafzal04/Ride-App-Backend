<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\UserType\UserType;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'user_type',
        'email_verified_at',
        'status',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'user_type'  => UserType::class,
        'status'     => Status::class,
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    public function isDriver(): bool
    {
        return $this->user_type === UserType::DRIVER;
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }
}
