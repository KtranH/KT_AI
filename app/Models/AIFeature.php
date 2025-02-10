<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIFeature extends Model
{
    protected $table = 'ai_features';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'prompt_template',
        'credit_cost',
        'count_img',
        'thumbnail_url',
        'input_requirements',
        'category',
        'sum_img',
        'average_processing_time',
        'status_feature'
    ];

    protected $casts = [
        'input_requirements' => 'json',
        'credit_cost' => 'integer',
        'count_img' => 'integer',
        'sum_img' => 'integer',
        'average_processing_time' => 'integer',
        'status_feature' => 'string'
    ];

    // Relationship với Image (1-nhiều)
    public function images()
    {
        return $this->hasMany(Image::class, 'features_id');
    }
} 