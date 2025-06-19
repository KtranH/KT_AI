<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'ip_address',
        'user_agent',
        'last_activity',
        'expires_at',
        'is_valid'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'expires_at' => 'datetime',
        'is_valid' => 'boolean'
    ];

    // Relationship với User (nhiều-1)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 