<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'profile_image',   // was profile_photo — actual column is profile_image
        'dob',             // was date_of_birth — actual column is dob
        'gender',
        'city',
        'address',
        'bio',             // exists in DB, was missing from model
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
