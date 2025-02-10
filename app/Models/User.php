<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'cover_image_url',
        'sum_like',
        'sum_img',
        'remaining_credits',
        'status_user'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationship với UserSession (1-nhiều)
    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    // Relationship với Image (1-nhiều)
    public function images()
    {
        return $this->hasMany(Image::class);
    }

    // Relationship với Comment (1-nhiều)
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Relationship với Notification (1-nhiều)
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Relationship với Interaction (1-nhiều)
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }
}
