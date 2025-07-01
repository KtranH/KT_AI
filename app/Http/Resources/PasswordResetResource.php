<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class PasswordResetResource extends JsonResource
{
    protected $step;
    protected $email;
    protected $token;
    protected $success;
    protected $message;

    /**
     * Tạo instance resource mới
     * 
     * @param mixed $resource
     * @param string $step
     * @param string|null $email
     * @param string|null $token
     * @param bool $success
     * @param string|null $message
     */
    public function __construct($resource, $step = 'send_code', $email = null, $token = null, $success = true, $message = null)
    {
        parent::__construct($resource);
        $this->step = $step;
        $this->email = $email;
        $this->token = $token;
        $this->success = $success;
        $this->message = $message;
    }

    /**
     * Chuyển đổi password reset data thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin password reset
     */
    public function toArray($request): array
    {
        $baseResponse = [
            'success' => $this->success,
            'message' => $this->message ?? $this->getDefaultMessage(),
            'step' => $this->step,
            'email' => $this->maskEmail($this->email),
            'timestamp' => now()->format('d/m/Y H:i:s'),
        ];

        // Thêm token nếu có (chỉ cho step verify_code)
        if ($this->step === 'verify_code' && $this->token) {
            $baseResponse['reset_token'] = $this->token;
            $baseResponse['token_expires_in'] = 600; // 10 phút
        }

        $baseResponse['next_step'] = $this->getNextStep();

        return $baseResponse;
    }

    /**
     * Tạo response cho gửi mã reset
     */
    public static function sendCode($email, $message = 'Chúng tôi đã gửi mã xác nhận đến email của bạn'): self
    {
        return new static(null, 'send_code', $email, null, true, $message);
    }

    /**
     * Tạo response cho verify code thành công
     */
    public static function verifyCodeSuccess($email, $token, $message = 'Mã xác thực hợp lệ'): self
    {
        return new static(null, 'verify_code', $email, $token, true, $message);
    }

    /**
     * Tạo response cho reset password thành công
     */
    public static function resetSuccess($email, $message = 'Mật khẩu đã được đặt lại thành công'): self
    {
        return new static(null, 'reset_complete', $email, null, true, $message);
    }

    /**
     * Tạo response cho các bước thất bại
     */
    public static function failed($step, $email = null, $message = 'Có lỗi xảy ra'): array
    {
        return [
            'success' => false,
            'message' => $message,
            'step' => $step,
            'email' => self::maskEmailStatic($email),
            'timestamp' => now()->format('d/m/Y H:i:s'),
            'next_step' => self::getFailedNextStep($step),
            'error_code' => self::getErrorCode($step),
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
     * Lấy message mặc định
     */
    private function getDefaultMessage(): string
    {
        return match($this->step) {
            'send_code' => 'Mã xác nhận đã được gửi đến email',
            'verify_code' => 'Mã xác thực hợp lệ',
            'reset_complete' => 'Mật khẩu đã được đặt lại thành công',
            default => 'Thao tác thành công'
        };
    }

    /**
     * Lấy hướng dẫn bước tiếp theo
     */
    private function getNextStep(): string
    {
        return match($this->step) {
            'send_code' => 'Kiểm tra email và nhập mã xác nhận 6 số',
            'verify_code' => 'Nhập mật khẩu mới để hoàn tất đặt lại',
            'reset_complete' => 'Đăng nhập với mật khẩu mới',
            default => 'Tiếp tục quy trình'
        };
    }

    /**
     * Lấy hướng dẫn khi thất bại
     */
    private static function getFailedNextStep($step): string
    {
        return match($step) {
            'send_code' => 'Kiểm tra email và thử lại',
            'verify_code' => 'Kiểm tra lại mã xác nhận hoặc yêu cầu gửi lại',
            'reset_complete' => 'Thử lại hoặc liên hệ hỗ trợ',
            default => 'Vui lòng thử lại'
        };
    }

    /**
     * Lấy error code
     */
    private static function getErrorCode($step): string
    {
        return match($step) {
            'send_code' => 'SEND_CODE_FAILED',
            'verify_code' => 'INVALID_CODE',
            'reset_complete' => 'RESET_FAILED',
            default => 'UNKNOWN_ERROR'
        };
    }
} 