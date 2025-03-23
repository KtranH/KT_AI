<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'user_id',
        'features_id',
        'prompt',
        'image_url',
        'sum_like',
        'sum_comment',
        'privacy_status',
        'metadata',
        'status_image',
        'list_like'
    ];

    protected $casts = [
        'metadata' => 'json',
        'privacy_status' => 'string',
        'status_image' => 'string'
    ];

    // Relationship với User (nhiều-1)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship với AIFeature (nhiều-1)
    public function aiFeature()
    {
        return $this->belongsTo(AIFeature::class, 'features_id');
    }

    // Relationship với Comment (1-nhiều)
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Relationship với Interaction (1-nhiều)
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    // Relationship với Report (1-nhiều)
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
} 