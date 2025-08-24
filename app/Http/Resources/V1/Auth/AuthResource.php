<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Auth;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class AuthResource extends BaseResource
{
    protected ?string $token;
    protected string $tokenType;
    protected bool $remember;
    protected ?int $expiresIn;
    protected ?string $message;
    protected ?array $logoutData;
    
    protected ?string $challengeId;
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
    public function __construct($resource, ?string $token = null, string $tokenType = 'Bearer', bool $remember = false, ?int $expiresIn = null, ?string $message = null, ?string $challengeId = null)
    {
        parent::__construct($resource);
        $this->token = $token;
        $this->tokenType = $tokenType;
        $this->remember = $remember;
        $this->expiresIn = $expiresIn;
        $this->message = $message;
        $this->logoutData = null;
        $this->challengeId = $challengeId;
    }

    /**
     * Chuyển đổi authentication data thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin authentication
     */
    public function toArray(Request $request): array
    {
        // Nếu là logout response, trả về data logout
        if ($this->logoutData !== null) {
            return $this->logoutData;
        }
        
        // Nếu không có resource (user), trả về response rỗng
        if (!$this->resource) {
            return [
                'success' => false,
                'message' => 'Không có dữ liệu user',
            ];
        }
        
        return [
            'success' => true,
            'message' => $this->message ?? 'Đăng nhập thành công',
            'user' => [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
                'email' => $this->resource->email,
                'email_verified_at' => $this->formatTimestamp($this->resource->email_verified_at),
                'is_verified' => $this->resource->is_verified,
                'avatar_url' => $this->formatUrl($this->resource->avatar_url),
                'cover_image_url' => $this->formatUrl($this->resource->cover_image_url),
                'remaining_credits' => $this->resource->remaining_credits ?? 0,
                'sum_like' => $this->resource->sum_like ?? 0,
                'sum_img' => $this->resource->sum_img ?? 0,
                'created_at' => $this->formatTimestamp($this->resource->created_at),
                'updated_at' => $this->formatTimestamp($this->resource->updated_at),
                'last_login' => $this->resource->updated_at?->format('d/m/Y H:i'),
            ],
            'auth' => [
                'token' => $this->token,
                'token_type' => $this->tokenType,
                'expires_in' => $this->expiresIn,
                'remember' => $this->remember,
            ],
            'permissions' => [
                'can_generate_image' => $this->resource->is_verified && ($this->resource->remaining_credits ?? 0) > 0,
                'can_comment' => $this->resource->is_verified,
                'can_like' => $this->resource->is_verified,
            ],
            'challenge_id' => $this->challengeId,
        ];
    }

    /**
     * Tạo response cho registration
     */
    public static function registration($user, string $message = 'Đăng ký thành công'): array
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
    public static function logout(string $message = 'Đăng xuất thành công', ?string $csrfToken = null): self
    {
        // Tạo instance mới với resource là null (vì user đã logout)
        $instance = new self(null);
        $instance->message = $message;
        
        // Lưu thông tin logout vào instance
        $instance->logoutData = [
            'success' => true,
            'message' => $message,
            'logged_out_at' => now()->format('d/m/Y H:i'),
            'csrf_token' => $csrfToken,
        ];
        
        return $instance;
    }

    /**
     * Tạo response cho verification
     */
    public static function verification(string $message = 'Xác thực thành công'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'verified_at' => now()->format('d/m/Y H:i'),
        ];
    }
} 