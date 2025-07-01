<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class MailVerificationResource extends JsonResource
{
    protected $verificationType;
    protected $email;
    protected $success;
    protected $message;
    protected $expiresIn;

    /**
     * Tạo instance resource mới
     * 
     * @param mixed $resource
     * @param string $verificationType
     * @param string $email
     * @param bool $success
     * @param string|null $message
     * @param int $expiresIn
     */
    public function __construct($resource, $verificationType = 'email', $email = null, $success = true, $message = null, $expiresIn = 600)
    {
        parent::__construct($resource);
        $this->verificationType = $verificationType;
        $this->email = $email;
        $this->success = $success;
        $this->message = $message;
        $this->expiresIn = $expiresIn;
    }

    /**
     * Chuyển đổi mail verification data thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin verification
     */
    public function toArray($request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message ?? $this->getDefaultMessage(),
            'verification_type' => $this->verificationType,
            'email' => $this->maskEmail($this->email),
            'expires_in' => $this->expiresIn,
            'expires_in_formatted' => $this->formatExpiration($this->expiresIn),
            'sent_at' => now()->format('d/m/Y H:i:s'),
            'next_step' => $this->getNextStep(),
        ];
    }

    /**
     * Tạo response cho email verification
     */
    public static function emailVerification($email, $message = 'Mã xác thực đã được gửi đến email của bạn'): self
    {
        return new static(null, 'email_verification', $email, true, $message);
    }

    /**
     * Tạo response cho password change verification
     */
    public static function passwordChangeVerification($email, $message = 'Mã xác thực đổi mật khẩu đã được gửi'): self
    {
        return new static(null, 'password_change', $email, true, $message);
    }

    /**
     * Tạo response cho resend verification
     */
    public static function resendVerification($email, $message = 'Đã gửi lại mã xác thực'): self
    {
        return new static(null, 'resend_verification', $email, true, $message);
    }

    /**
     * Tạo response cho verification thất bại
     */
    public static function failed($email, $verificationType = 'email', $message = 'Gửi mã xác thực thất bại'): array
    {
        return [
            'success' => false,
            'message' => $message,
            'verification_type' => $verificationType,
            'email' => self::maskEmailStatic($email),
            'expires_in' => null,
            'sent_at' => now()->format('d/m/Y H:i:s'),
            'next_step' => 'Vui lòng thử lại sau',
        ];
    }

    /**
     * Mask email để bảo mật
     */
    private function maskEmail($email): ?string
    {
        return self::maskEmailStatic($email);
    }

    /**
     * Static method để mask email
     */
    private static function maskEmailStatic($email): ?string
    {
        if (!$email) return null;

        $parts = explode('@', $email);
        if (count($parts) !== 2) return $email;

        $username = $parts[0];
        $domain = $parts[1];

        $maskedUsername = strlen($username) > 2 
            ? substr($username, 0, 2) . str_repeat('*', strlen($username) - 2)
            : str_repeat('*', strlen($username));

        return $maskedUsername . '@' . $domain;
    }

    /**
     * Format thời gian hết hạn
     */
    private function formatExpiration($seconds): string
    {
        $minutes = floor($seconds / 60);
        return $minutes . ' phút';
    }

    /**
     * Lấy message mặc định
     */
    private function getDefaultMessage(): string
    {
        return match($this->verificationType) {
            'email_verification' => 'Mã xác thực email đã được gửi',
            'password_change' => 'Mã xác thực đổi mật khẩu đã được gửi',
            'resend_verification' => 'Đã gửi lại mã xác thực',
            default => 'Mã xác thực đã được gửi'
        };
    }

    /**
     * Lấy hướng dẫn bước tiếp theo
     */
    private function getNextStep(): string
    {
        return match($this->verificationType) {
            'email_verification' => 'Vui lòng kiểm tra email và nhập mã xác thực',
            'password_change' => 'Nhập mã xác thực để xác nhận đổi mật khẩu',
            'resend_verification' => 'Vui lòng kiểm tra email và nhập mã xác thực mới',
            default => 'Vui lòng kiểm tra email'
        };
    }
} 