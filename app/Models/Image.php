<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'features_id',
        'title',
        'prompt',
        'image_url',
        'sum_like',
        'sum_comment',
        'privacy_status',
        'metadata',
        'list_like'
    ];

    protected $casts = [
        'image_url' => 'array',
        'sum_like' => 'integer',
        'sum_comment' => 'integer',
        'metadata' => 'json',
        'privacy_status' => 'string',
    ];

    // Relationship với User (nhiều-1)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship với AIFeature (nhiều-1)
    public function aiFeature()
    {
        return $this->belongsTo(AIFeature::class, 'features_id');
    }

    // Relationship với Comment (1-nhiều)
    public function comments(): HasMany
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