<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AuthResource extends JsonResource
{
    protected $token;
    protected $tokenType;
    protected $remember;
    protected $expiresIn;
    protected $message;

    /**
     * Tạo instance resource mới
     * 
     * @param mixed $resource
     * @param string|null $token
     * @param string $tokenType
     * @param bool $remember
     * @param int|null $expiresIn
     * @param string|null $message
     */
    public function __construct($resource, $token = null, $tokenType = 'Bearer', $remember = false, $expiresIn = null, $message = null)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->remember = $remember;
        $this->expiresIn = $expiresIn;
        $this->message = $message;
    }

    /**
     * Chuyển đổi authentication data thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin authentication
     */
    public function toArray($request): array
    {
        return [
            'success' => true,
            'message' => $this->message ?? 'Đăng nhập thành công',
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'email_verified_at' => $this->email_verified_at,
                'is_verified' => $this->is_verified,
                'avatar_url' => $this->avatar_url,
                'cover_image_url' => $this->cover_image_url,
                'remaining_credits' => $this->remaining_credits ?? 0,
                'sum_like' => $this->sum_like ?? 0,
                'sum_img' => $this->sum_img ?? 0,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'last_login' => $this->updated_at?->format('d/m/Y H:i'),
            ],
            'auth' => [
                'token' => $this->token,
                'token_type' => $this->tokenType,
                'expires_in' => $this->expiresIn,
                'remember' => $this->remember,
            ],
            'permissions' => [
                'can_generate_image' => $this->is_verified && ($this->remaining_credits ?? 0) > 0,
                'can_comment' => $this->is_verified,
                'can_like' => $this->is_verified,
            ],
        ];
    }

    /**
     * Tạo response cho registration
     */
    public static function registration($user, $message = 'Đăng ký thành công'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'user_id' => $user->id,
            'email' => $user->email,
            'verification_sent' => true,
            'next_step' => 'Vui lòng kiểm tra email để xác thực tài khoản',
        ];
    }

    /**
     * Tạo response cho logout
     */
    public static function logout($message = 'Đăng xuất thành công'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'logged_out_at' => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Tạo response cho verification
     */
    public static function verification($message = 'Xác thực thành công'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'verified_at' => now()->format('d/m/Y H:i'),
        ];
    }
} 