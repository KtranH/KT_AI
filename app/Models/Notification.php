<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'json',
        'read_at' => 'datetime'
    ];

    // Relationship với User (nhiều-1)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 