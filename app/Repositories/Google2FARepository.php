<?php

namespace App\Repositories;

use App\Interfaces\Google2FARepositoryInterface;
use App\Models\Google2FASecret;
use App\Models\RecoveryCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class Google2FARepository implements Google2FARepositoryInterface
{
    /**
     * Lấy trạng thái 2FA của user
     */
    public function getStatus(User $user): ?Google2FASecret
    {
        return Google2FASecret::where('user_id', $user->id)->first();
    }

    /**
     * Kiểm tra xem user có 2FA được bật không
     */
    public function isEnabled(User $user): bool
    {
        $secret = $this->getEnabledSecret($user);
        return $secret && $secret->is_enabled;
    }

    /**
     * Lưu secret key tạm thời
     */
    public function saveTemporarySecret(User $user, string $secretKey): Google2FASecret
    {
        // Xóa secret cũ nếu có
        Google2FASecret::where('user_id', $user->id)->delete();
        
        // Tạo secret mới
        return Google2FASecret::create([
            'user_id' => $user->id,
            'secret_key' => $secretKey,
            'is_enabled' => false,
        ]);
    }

    /**
     * Lấy secret key tạm thời
     */
    public function getTemporarySecret(User $user): ?Google2FASecret
    {
        return Google2FASecret::where('user_id', $user->id)->where('is_enabled', false)->first();
    }

    /**
     * Lấy secret key đã kích hoạt
     */
    public function getEnabledSecret(User $user): ?Google2FASecret
    {
        return Google2FASecret::where('user_id', $user->id)->where('is_enabled', true)->first();
    }

    /**
     * Tạo mã QR cho 2FA
     */
    public function generateQRCode(User $user, string $secretKey): Google2FASecret
    {
        return Google2FASecret::create([
            'user_id' => $user->id,
            'secret_key' => $secretKey,
        ]);
    }   

    /**
     * Tạo mã khôi phục
     */
    public function generateRecoveryCodes(User $user): void
    {
        // Tạo 10 mã khôi phục
        for ($i = 0; $i < 10; $i++) {
            RecoveryCode::create([
                'user_id' => $user->id,
                'code' => Str::random(10),
                'used' => false,
            ]);
        }
    }

    /**
     * Lấy mã khôi phục
     */
    public function getRecoveryCodes(int $userId): Collection
    {
        return RecoveryCode::where('user_id', $userId)->get();
    }

    /**
     * Sử dụng mã khôi phục
     */
    public function useRecoveryCode(int $userId, string $code): ?RecoveryCode
    {
        return RecoveryCode::where('user_id', $userId)
            ->where('code', $code)
            ->where('used', false)
            ->first();
    }

    /**
     * Xóa mã khôi phục cũ
     */
    public function deleteOldRecoveryCodes(User $user): void
    {
        RecoveryCode::where('user_id', $user->id)->delete();
    }
}   