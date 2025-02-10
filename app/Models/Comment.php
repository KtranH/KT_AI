<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'image_id',
        'parent_id',
        'content'
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

    // Self relationship cho parent comment
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Self relationship cho child comments
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
} 