<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIFeature extends Model
{
    use HasFactory;

    protected $table = 'ai_features';

    protected $fillable = [
        'title',
        'description',
        'category',
        'thumbnail_url',
        'status_feature'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
} 