<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $fillable = [
        'user_id',
        'image_id',
        'type_interaction',
        'content',
        'status_interaction'
    ];

    protected $casts = [
        'type_interaction' => 'string',
        'status_interaction' => 'string'
    ];

    // Relationship với User (nhiều-1)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship với Image (nhiều-1)
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
} 