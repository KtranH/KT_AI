<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Google2FASecret;
use App\Models\RecoveryCode;

interface Google2FARepositoryInterface
{
    /**
     * Lấy trạng thái 2FA của user
     */
    public function getStatus(User $user): ?Google2FASecret;

    /**
     * Kiểm tra xem user có 2FA được bật không
     */
    public function isEnabled(User $user): bool;

    /**
     * Lưu secret key tạm thời
     */
    public function saveTemporarySecret(User $user, string $secretKey);

    /**
     * Lấy secret key tạm thời
     */
    public function getTemporarySecret(User $user): ?Google2FASecret;

    /**
     * Lấy secret key đã kích hoạt
     */
    public function getEnabledSecret(User $user): ?Google2FASecret;

    /**
     * Tạo mã QR cho 2FA
     */
    public function generateQRCode(User $user, string $secretKey): Google2FASecret;

    /**
     * Tạo mã khôi phục
     */
    public function generateRecoveryCodes(User $user): void;

    /**
     * Lấy mã khôi phục
     */
    public function getRecoveryCodes(int $userId): Collection;

    /**
     * Sử dụng mã khôi phục
     */
    public function useRecoveryCode(int $userId, string $code): ?RecoveryCode;

    /**
     * Xóa mã cũ
     */
    public function deleteOldRecoveryCodes(User $user): void;
    
}