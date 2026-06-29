<?php

namespace App\Models;

use App\Constants\ResourceFields;
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
        'phone_number',
        'email',
        'password',
        'user_type',
        'status',
        'city_id',
        'is_admin',
        'phone_verified_at',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'user_type'          => UserType::class,
        'status'             => Status::class,
        'password'           => 'hashed',
        'is_admin'           => 'boolean',
        'phone_verified_at'  => 'datetime',
        'email_verified_at'  => 'datetime',
        'last_login_at'      => 'datetime',
    ];


    public const RESOURCE_RELATIONS = [
        'profile' => [
            'select' => ResourceFields::USER_PROFILE_FIELDS,
            'show'   => ResourceFields::USER_PROFILE_FIELDS,
        ],
        'driverProfile' => [
            'select' => ResourceFields::DRIVER_PROFILE_FIELDS,
            'show'   => ResourceFields::DRIVER_PROFILE_FIELDS,
        ],
        'vehicles' => [
            'select' => ResourceFields::VEHICLE_FIELDS,
            'show'   => ResourceFields::VEHICLE_FIELDS,
        ],
        'vehicles.vehicleModel' => [
            'select' => ResourceFields::VEHICLE_MODEL_FIELDS,
            'show'   => ResourceFields::VEHICLE_MODEL_FIELDS,
        ],
        'vehicles.vehicleModel.make' => [
            'select' => ResourceFields::VEHICLE_MAKE_FIELDS,
            'show'   => ResourceFields::VEHICLE_MAKE_FIELDS,
        ],
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
    public function ridePosts(): HasMany
    {
        return $this->hasMany(RidePost::class, 'driver_id');
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

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
