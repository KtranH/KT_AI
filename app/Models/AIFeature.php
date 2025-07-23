<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIFeature extends Model
{
    use HasFactory;

    protected $table = 'ai_features';

    protected $fillable = [
        'id',
        'title',
        'description',
        'category',
        'thumbnail_url',
        'status_feature',
        'input_requirements',
        'sum_img'
    ];

    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'category' => 'string',
        'thumbnail_url' => 'string',
        'status_feature' => 'string',
        'input_requirements' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'sum_img' => 'integer'
    ];
} 