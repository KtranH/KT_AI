<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'status_user',
        'activities'
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

    /**
     * Gửi thông báo đặt lại mật khẩu
     *
     * @param string $code
     * @return void
     */
    public function sendPasswordResetNotification($code): void
    {
        $this->notify(new ResetPasswordNotification($code));
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

    // Relationship với Interaction (1-nhiều)
    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }
}
