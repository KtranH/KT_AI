<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image_id',
        'parent_id',
        'origin_comment',
        'content',
        'sum_like',
        'list_like'
    ];

    protected $casts = [
        'list_like' => 'array',
        'sum_like' => 'integer'
    ];

    // Relationship với User (nhiều-1)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship với Image (nhiều-1)
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    // Self relationship cho parent comment
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Self relationship cho child comments
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Self relationship cho origin comment
    public function originComment()
    {
        return $this->belongsTo(Comment::class, 'origin_comment');
    }

    // Self relationship cho các replies của origin comment
    public function originReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'origin_comment');
    }
}