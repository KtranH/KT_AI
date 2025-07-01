<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ExternalServiceException;
use App\Exceptions\BusinessException;
use App\Interfaces\UserRepositoryInterface;
use App\Mail\VerificationMail;
use App\Http\Resources\AuthResource;
use App\Http\Resources\MailVerificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MailService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Gửi email xác thực ban đầu
     */
    public function sendMail($user): bool
    {
        return $this->executeWithExceptionHandling(function() use ($user) {
            $verificationcode = Str::random(6);
            Redis::setex("verify_code:{$user->email}", 600, $verificationcode);
            
            $detail = [
                'title' => "Mã xác nhận từ KT-AI",
                'begin' => "Xin chào " . $user->name,
                'body' => "Chúng tôi nhận được lượt đăng ký tài khoản của bạn. Nhập mã dưới đây để hoàn tất xác minh (Lưu ý mã chỉ có khả dụng trong 10 phút)",
                'code' => $verificationcode
            ];
            
            Mail::to($user->email)->send(new VerificationMail($detail));
            
            $this->logAction('Verification email sent', [
                'email' => $user->email,
                'code_length' => strlen($verificationcode)
            ]);
            
            return true;
        }, "Sending verification email to: {$user->email}");
    }

    /**
     * Gửi lại email xác thực
     */
    public function sendMailResend($user): bool
    {
        return $this->executeWithExceptionHandling(function() use ($user) {
            $verificationcode = Str::random(6);
            Redis::setex("verify_code:{$user->email}", 600, $verificationcode);
            
            $detail = [
                'title' => "Mã xác nhận từ KT-AI",
                'begin' => "Xin chào " . $user->name,
                'body' => "Chúng tôi nhận được yêu cầu gửi lại mã xác thực. Nhập mã dưới đây để hoàn tất xác minh (Lưu ý mã chỉ có khả dụng trong 10 phút)",
                'code' => $verificationcode
            ];
            
            Mail::to($user->email)->send(new VerificationMail($detail));
            
            $this->logAction('Verification email resent', [
                'email' => $user->email
            ]);
            
            return true;
        }, "Resending verification email to: {$user->email}");
    }

    /**
     * Gửi email xác thực đổi mật khẩu
     */
    public function sendPasswordChangeVerification($user): bool
    {
        return $this->executeWithExceptionHandling(function() use ($user) {
            $verificationcode = Str::random(6);
            Redis::setex("password_change_code:{$user->email}", 600, $verificationcode);
            
            $detail = [
                'title' => "Mã xác nhận đổi mật khẩu từ KT-AI",
                'begin' => "Xin chào " . $user->name,
                'body' => "Chúng tôi nhận được yêu cầu đổi mật khẩu từ tài khoản của bạn. Nhập mã dưới đây để xác nhận việc đổi mật khẩu (Lưu ý mã chỉ có khả dụng trong 10 phút)",
                'code' => $verificationcode
            ];
            
            Mail::to($user->email)->send(new VerificationMail($detail));
            
            $this->logAction('Password change verification email sent', [
                'email' => $user->email
            ]);
            
            return true;
        }, "Sending password change verification to: {$user->email}");
    }

    /**
     * Xác thực email với mã xác nhận
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        return $this->executeInTransactionSafely(function () use ($request) {
            $request->validate([
                'email' => ['required', 'email'],
                'code' => ['required', 'string', 'size:6'],
            ]);

            $storedCode = Redis::get("verify_code:{$request->email}");

            if (!$storedCode) {
                throw ValidationException::withMessages([
                    'code' => ['Mã xác thực đã hết hạn hoặc không tồn tại.'],
                ]);
            }

            if ($request->code !== $storedCode) {
                throw ValidationException::withMessages([
                    'code' => ['Mã xác thực không đúng.'],
                ]);
            }

            $user = $this->userRepository->checkEmail($request->email);

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Không tìm thấy tài khoản.'],
                ]);
            }

            // Đăng nhập và cập nhật trạng thái
            Auth::login($user);
            $this->userRepository->updateStatus();

            // Xóa các key Redis
            $this->clearVerificationKeys($request->email);

            $this->logAction('Email verification successful', [
                'email' => $request->email,
                'user_id' => $user->id
            ]);

            return response()->json(AuthResource::verification('Xác thực email thành công.'));
        }, "Verifying email for: {$request->email}");
    }

    /**
     * Gửi lại mã xác thực với rate limiting
     */
    public function resendVerification(Request $request): JsonResponse
    {
        return $this->executeInTransactionSafely(function () use ($request) {
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            // Kiểm tra rate limiting
            $this->checkResendRateLimit($request->email);

            $user = $this->userRepository->getUserByEmail($request->email);

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Không tìm thấy tài khoản.'],
                ]);
            }

            if ($user->is_verified) {
                throw ValidationException::withMessages([
                    'email' => ['Email này đã được xác thực.'],
                ]);
            }

            // Gửi email và cập nhật counters
            if (!$this->sendMailResend($user)) {
                throw ExternalServiceException::emailServiceError('Failed to resend verification code');
            }

            $this->updateResendCounters($request->email);

            return response()->json(
                MailVerificationResource::resendVerification(
                    $request->email,
                    'Đã gửi lại mã xác thực'
                )
            );
        }, "Resending verification for: {$request->email}");
    }

    /**
     * Gửi mã xác thực đổi mật khẩu
     */
    public function sendPasswordChangeVerificationCode(): JsonResponse
    {
        return $this->executeWithExceptionHandling(function() {
            $user = Auth::user();

            if (!$user) {
                throw BusinessException::invalidPermissions('password change (not authenticated)');
            }

            // Kiểm tra rate limiting
            $this->checkPasswordChangeRateLimit($user->email);

            // Gửi mã xác thực
            if (!$this->sendPasswordChangeVerification($user)) {
                throw ExternalServiceException::emailServiceError('Failed to send verification code');
            }

            // Lưu thời gian gửi
            Redis::setex("password_change_last_sent:{$user->email}", 600, time());

            return response()->json(
                MailVerificationResource::passwordChangeVerification(
                    $user->email,
                    'Đã gửi mã xác thực đến email của bạn'
                )
            );
        }, "Sending password change verification code for user ID: " . Auth::id());
    }

    /**
     * Kiểm tra rate limiting cho resend verification
     */
    private function checkResendRateLimit(string $email): void
    {
        $resendCount = Redis::get("verify_resend_count:{$email}") ?: 0;
        
        if ($resendCount === 1) {
            Redis::expire("verify_resend_count:{$email}", 300); // 5 phút
        }

        if ($resendCount > 3) {
            $ttl = Redis::ttl("verify_resend_count:{$email}");
            throw ValidationException::withMessages([
                'email' => ["Bạn đã vượt quá số lần gửi lại mã cho phép. Vui lòng thử lại sau {$ttl} giây."]
            ]);
        }

        $lastResendTime = Redis::get("verify_last_resend:{$email}");
        if ($lastResendTime && (time() - $lastResendTime) < 60) {
            throw ValidationException::withMessages([
                'email' => ['Vui lòng đợi 60 giây trước khi gửi lại mã.'],
            ]);
        }
    }

    /**
     * Kiểm tra rate limiting cho password change verification
     */
    private function checkPasswordChangeRateLimit(string $email): void
    {
        $lastSentTime = Redis::get("password_change_last_sent:{$email}");
        if ($lastSentTime && (time() - $lastSentTime) < 60) {
            throw BusinessException::quotaExceeded('password change verification attempts', 60);
        }
    }

    /**
     * Cập nhật counters cho resend
     */
    private function updateResendCounters(string $email): void
    {
        Redis::incr("verify_resend_count:{$email}");
        Redis::setex("verify_last_resend:{$email}", 600, time());
    }

    /**
     * Xóa các key Redis liên quan đến verification
     */
    private function clearVerificationKeys(string $email): void
    {
        Redis::del("verify_code:{$email}");
        Redis::del("verify_attempts:{$email}");
        Redis::del("verify_last_attempt:{$email}");
    }
}
