<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'image_id',
        'type_interaction',
        'content',
        'status_interaction'
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'image_id' => 'integer',
        'type_interaction' => 'string',
        'content' => 'string',
        'status_interaction' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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