<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'input_requirements' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'sum_img' => 'integer'
    ];

    // Relationship với Image (1-nhiều)
    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }
} 