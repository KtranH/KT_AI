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
        'id',
        'name',
        'email',
        'password',
        'avatar_url',
        'cover_image_url',
        'sum_like',
        'sum_img',
        'remaining_credits',
        'status_user',
        'activities',
        'is_verified'
    ];

    /**
     * Các thuộc tính cần ẩn khi serialize
     * @var list<string> Danh sách các thuộc tính cần ẩn
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Các thuộc tính cần ép kiểu
     * @return array<string, string> Danh sách các thuộc tính cần ép kiểu
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'activities' => 'array',
            'email' => 'string',
            'name' => 'string',
            'avatar_url' => 'string',
            'cover_image_url' => 'string',
            'sum_like' => 'integer',
            'sum_img' => 'integer',
            'remaining_credits' => 'integer',
        ];
    }

    /**
     * Gửi thông báo đặt lại mật khẩu
     * @param string $code Mã xác thực
     * @return void
     */
    public function sendPasswordResetNotification($code): void
    {
        $this->notify(new ResetPasswordNotification($code));
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
