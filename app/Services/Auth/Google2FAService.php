<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use App\Models\RecoveryCode;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use App\Interfaces\Google2FARepositoryInterface;
// use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Google2FAService extends BaseService
{
    private Google2FA $google2FA;
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 300; // 5 phút
    private const TOTP_WINDOW = 2; // Chấp nhận mã trong ±2 khoảng thời gian (60 giây)

    public function __construct(
        private readonly Google2FARepositoryInterface $google2FARepository
    ) {
        $this->google2FA = new Google2FA();
        // Cấu hình window tolerance cho TOTP
        $this->google2FA->setWindow(self::TOTP_WINDOW);
    }

    /**
     * Kiểm tra xem user có 2FA được bật không
     */
    public function isEnabled(User $user): bool
    {
        $secret = $this->google2FARepository->getEnabledSecret($user);
        return $secret && $secret->is_enabled;
    }

    /**
     * Lấy trạng thái 2FA của user
     */
    public function getStatus(User $user): array
    {
        $secret = $this->google2FARepository->getStatus($user);
        
        $status = [
            'is_enabled' => $secret && $secret->is_enabled,
            'recovery_codes' => $this->getRecoveryCodes($user->id),
            'created_at' => $secret?->created_at,
            'updated_at' => $secret?->updated_at,
        ];
        
        return $status;
    }

    /**
     * Tạo mã QR và secret key để setup 2FA
     */
    public function generateQRCode(User $user): array
    {
        // Tạo secret key mới
        $secretKey = $this->google2FA->generateSecretKey();
        
        // Tạo QR code URL
        $qrCodeUrl = $this->google2FA->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );
        
        // Lưu secret key tạm thời (chưa enable)
        $this->google2FARepository->saveTemporarySecret($user, $secretKey);
        
        return [
            'secret_key' => $secretKey,
            'qr_code_url' => $qrCodeUrl,
            'qr_code_image' => null, // Sẽ tạo QR code ở frontend
        ];
    }

    /**
     * Bật 2FA với mã xác thực
     */
    public function enable(User $user, array $data): array
    {
        $code = $this->validateAndSanitizeCode($data['code'] ?? '');
        
        // Lấy secret key tạm thời
        $secret = $this->google2FARepository->getTemporarySecret($user);
            
        if (!$secret) {
            throw new \Exception('Không tìm thấy secret key. Vui lòng tạo lại mã QR.');
        }
        
        // Xác thực mã
        if (!$this->google2FA->verifyKey($secret->secret_key, $code)) {
            throw new \Exception('Mã xác thực không đúng.');
        }
        
        try {
            DB::beginTransaction();
            
            // Kích hoạt 2FA
            $secret->update(['is_enabled' => true]);
            
            // Tạo mã khôi phục
            $this->generateRecoveryCodes($user);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => '2FA đã được kích hoạt thành công.',
                'recovery_codes' => $this->getRecoveryCodes($user->id),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Không thể kích hoạt 2FA: ' . $e->getMessage());
        }
    }

    /**
     * Tắt 2FA với mã xác thực
     */
    public function disable(User $user, array $data): array
    {
        $code = $this->validateAndSanitizeCode($data['code'] ?? '');
        
        // Lấy secret key đã kích hoạt
        $secret = $this->google2FARepository->getEnabledSecret($user);
            
        if (!$secret) {
            throw new \Exception('2FA chưa được kích hoạt.');
        }
        
        // Xác thực mã
        if (!$this->google2FA->verifyKey($secret->secret_key, $code)) {
            throw new \Exception('Mã xác thực không đúng.');
        }
        
        try {
            DB::beginTransaction();
            
            // Tắt 2FA
            $secret->delete();
            // Xóa mã khôi phục
            RecoveryCode::where('user_id', $user->id)->delete();
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => '2FA đã được tắt thành công.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Không thể tắt 2FA: ' . $e->getMessage());
        }
    }

    /**
     * Tạo mã khôi phục mới
     */
    public function generateRecoveryCodes(User $user): array
    {
        // Xóa mã cũ
        $this->google2FARepository->deleteOldRecoveryCodes($user);
        
        // Tạo mã mới
        $this->google2FARepository->generateRecoveryCodes($user);
        
        // Lấy mã mới tạo
        $newCodes = $this->getRecoveryCodes($user->id);
        
        return [
            'success' => true,
            'message' => 'Mã khôi phục mới đã được tạo thành công.',
            'recovery_codes' => $newCodes,
        ];
    }

    /**
     * Kiểm tra xem có cần xác thực 2FA không
     */
    public function requiresVerification(User $user): bool
    {
        return $this->isEnabled($user);
    }

    /**
     * Xác thực 2FA khi đăng nhập
     */
    public function verify(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $code = $this->validateAndSanitizeCode($data['code'] ?? '');
        
        if ($userId <= 0) {
            throw new \Exception('User ID không hợp lệ.');
        }

        // Kiểm tra user có 2FA được bật không
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User không tồn tại.');
        }
        
        if (!$this->requiresVerification($user)) {
            throw new \Exception('User này không cần xác thực 2FA.');
        }
        
        $secret = $this->google2FARepository->getEnabledSecret($user);
        if (!$secret) {
            throw new \Exception('2FA chưa được kích hoạt cho user này.');
        }

        // Kiểm tra rate limiting
        $this->checkRateLimit($userId);
        
        // Xác thực mã
        if (!$this->google2FA->verifyKey($secret->secret_key, $code)) {
            $this->incrementFailedAttempts($userId);
            throw new \Exception('Mã xác thực không đúng.');
        }

        // Reset failed attempts khi xác thực thành công
        $this->resetFailedAttempts($userId);
        
        return [
            'success' => true,
            'message' => 'Xác thực 2FA thành công.'
        ];
    }

    /**
     * Sử dụng mã khôi phục
     */
    public function useRecoveryCode(array $data): array
    {
        $userId = (int) ($data['user_id'] ?? 0);
        $code = trim($data['code'] ?? '');
        
        if ($userId <= 0 || empty($code)) {
            throw new \Exception('Thông tin không hợp lệ.');
        }

        $recoveryCode = $this->google2FARepository->useRecoveryCode($userId, $code);
            
        if (!$recoveryCode) {
            throw new \Exception('Mã khôi phục không đúng hoặc đã được sử dụng.');
        }

        // Đánh dấu mã đã sử dụng
        $recoveryCode->update(['used' => true]);

        return [
            'success' => true,
            'message' => 'Mã khôi phục đã được sử dụng thành công.'
        ];
    }

    /**
     * Sử dụng mã khôi phục với challenge_id để hoàn tất đăng nhập
     */
    public function useRecoveryCodeWithChallenge(array $data): array
    {
        $challengeId = $data['challenge_id'] ?? '';
        $code = trim($data['code'] ?? '');
        
        if (empty($challengeId)) {
            throw new \Exception('Challenge ID không hợp lệ.');
        }
        
        // Xác thực challenge từ cache
        // Nếu challengeId có prefix 2fa_challenge_, loại bỏ prefix
        $cacheKey = str_starts_with($challengeId, '2fa_challenge_') ? substr($challengeId, 15) : $challengeId;
        $challenge = Cache::get($cacheKey);
        if (!$challenge) {
            throw new \Exception('Challenge đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.');
        }
        
        // Kiểm tra IP để tăng bảo mật
        if ($challenge['ip'] !== request()->ip()) {
            Cache::forget($cacheKey);
            throw new \Exception('IP không khớp. Vui lòng đăng nhập lại.');
        }
        
        // Sử dụng mã khôi phục
        $recoveryCode = $this->google2FARepository->useRecoveryCode($challenge['user_id'], $code);
            
        if (!$recoveryCode) {
            throw new \Exception('Mã khôi phục không đúng hoặc đã được sử dụng.');
        }

        // Đánh dấu mã đã sử dụng
        $recoveryCode->update(['used' => true]);
        
        // Tìm user và tạo session
        $user = User::find($challenge['user_id']);
        if (!$user) {
            throw new \Exception('User không tồn tại.');
        }
        
        // Đăng nhập user
        Auth::login($user, $challenge['remember']);
        
        // Regenerate session để tăng bảo mật
        request()->session()->regenerate();
        
        // Xóa challenge sau khi xác thực thành công
        Cache::forget($cacheKey);
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Đăng nhập thành công với mã khôi phục',
            'auth_type' => 'Session_Recovery_Code'
        ];
    }

    /**
     * Xác thực 2FA khi đăng nhập và hoàn tất quá trình đăng nhập
     */
    public function verifyLogin2FA(array $data): array
    {
        $challengeId = $data['challenge_id'] ?? '';
        $code = $data['code'] ?? '';
        
        if (empty($challengeId)) {
            throw new \Exception('Challenge ID không hợp lệ.');
        }
        
        // Xác thực challenge từ cache
        $challenge = Cache::get($challengeId);
        if (!$challenge) {
            throw new \Exception('Challenge đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.');
        }
        
        // Kiểm tra IP để tăng bảo mật
        if ($challenge['ip'] !== request()->ip()) {
            Cache::forget($challengeId);
            throw new \Exception('IP không khớp. Vui lòng đăng nhập lại.');
        }
        
        // Xác thực mã 2FA
        $verifyData = [
            'user_id' => $challenge['user_id'],
            'code' => $code
        ];
        
        $result = $this->verify($verifyData);
        
        if ($result['success']) {
            // Tìm user và tạo session
            $user = User::find($challenge['user_id']);
            if (!$user) {
                throw new \Exception('User không tồn tại.');
            }
            
            // Đăng nhập user
            Auth::login($user, $challenge['remember']);
            
            // Regenerate session để tăng bảo mật
            request()->session()->regenerate();
            
            // Xóa challenge sau khi xác thực thành công
            Cache::forget($challengeId);
            
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Đăng nhập thành công với 2FA',
                'auth_type' => 'Session_2FA'
            ];
        }
        
        throw new \Exception('Xác thực 2FA thất bại.');
    }

    /**
     * Lấy mã khôi phục của user
     */
    private function getRecoveryCodes(int $userId): array
    {
        $recoveryCodes = $this->google2FARepository->getRecoveryCodes($userId);
        return $recoveryCodes->toArray();
    }

    /**
     * Validate và sanitize mã 2FA
     */
    private function validateAndSanitizeCode(string $code): string
    {
        $code = trim($code);
        
        if (empty($code)) {
            throw new \Exception('Mã xác thực không được để trống.');
        }
        
        if (!preg_match('/^\d{6}$/', $code)) {
            throw new \Exception('Mã xác thực phải là 6 chữ số.');
        }
        
        return $code;
    }

    /**
     * Kiểm tra rate limiting
     */
    private function checkRateLimit(int $userId): void
    {
        $attempts = $this->getFailedAttempts($userId);
        
        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutKey = "2fa_lockout_{$userId}";
            $lockoutTime = Cache::get($lockoutKey);
            
            if ($lockoutTime && (time() - $lockoutTime) < self::LOCKOUT_DURATION) {
                $remainingTime = self::LOCKOUT_DURATION - (time() - $lockoutTime);
                throw new \Exception("Tài khoản đã bị khóa do quá nhiều lần thử sai. Vui lòng thử lại sau {$remainingTime} giây.");
            }
            
            // Reset lockout nếu đã hết thời gian
            if ($lockoutTime) {
                Cache::forget($lockoutKey);
                $this->resetFailedAttempts($userId);
            }
        }
    }

    /**
     * Tăng số lần thử sai
     */
    private function incrementFailedAttempts(int $userId): void
    {
        $key = "2fa_failed_attempts_{$userId}";
        $currentAttempts = Cache::get($key, 0);
        
        // Đảm bảo currentAttempts là int
        $attempts = (int) $currentAttempts + 1;
        
        // Lưu vào cache với kiểu dữ liệu int
        Cache::put($key, $attempts, now()->addMinutes(30));
        
        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockoutKey = "2fa_lockout_{$userId}";
            Cache::put($lockoutKey, time(), now()->addSeconds(self::LOCKOUT_DURATION));
        }
    }

    /**
     * Lấy số lần thử sai
     */
    private function getFailedAttempts(int $userId): int
    {
        $key = "2fa_failed_attempts_{$userId}";
        $attempts = Cache::get($key, 0);
        
        // Đảm bảo trả về int, không phải string
        return (int) $attempts;
    }

    /**
     * Reset số lần thử sai
     */
    private function resetFailedAttempts(int $userId): void
    {
        $key = "2fa_failed_attempts_{$userId}";
        $lockoutKey = "2fa_lockout_{$userId}";
        
        Cache::forget($key);
        Cache::forget($lockoutKey);
    }
}
