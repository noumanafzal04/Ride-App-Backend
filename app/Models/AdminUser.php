<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'admin_users';

    protected $fillable = ['role_id', 'name', 'email', 'password', 'status', 'last_login_at', 'notifications_read_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password'              => 'hashed',
        'last_login_at'         => 'datetime',
        'notifications_read_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuper(): bool
    {
        return (bool) $this->role?->isSuper();
    }

    /** Flat list of permission keys this admin has. */
    public function permissionKeys(): array
    {
        if (!$this->relationLoaded('role')) {
            $this->load('role.permissions');
        }
        return $this->role
            ? $this->role->permissions->pluck('key')->all()
            : [];
    }

    public function hasPermission(string $key): bool
    {
        return $this->isSuper() || in_array($key, $this->permissionKeys(), true);
    }
}
