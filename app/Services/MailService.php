<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Mail\VerificationMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Exception;

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
        try {
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
        } catch (Exception $e) {
            Log::error('Lỗi khi gửi email xác nhận: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi lại email xác thực
     */
    public function sendMailResend($user): bool
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Lỗi khi gửi email xác nhận: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi email xác thực đổi mật khẩu
     */
    public function sendPasswordChangeVerification($user): bool
    {
        try {
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
        } catch (Exception $e) {
            Log::error('Lỗi khi gửi email xác nhận đổi mật khẩu: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Xác thực email với mã xác nhận
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        return $this->executeInTransaction(function () use ($request) {
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

            return response()->json([
                'message' => 'Xác thực email thành công.',
                'user' => $user
            ]);
        });
    }

    /**
     * Gửi lại mã xác thực với rate limiting
     */
    public function resendVerification(Request $request): JsonResponse
    {
        return $this->executeInTransaction(function () use ($request) {
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
                throw new Exception('Lỗi khi gửi mã xác thực');
            }

            $this->updateResendCounters($request->email);

            return response()->json([
                'message' => 'Đã gửi lại mã xác thực.',
            ]);
        });
    }

    /**
     * Gửi mã xác thực đổi mật khẩu
     */
    public function sendPasswordChangeVerificationCode(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new Exception('Người dùng chưa đăng nhập', 401);
            }

            // Kiểm tra rate limiting
            $this->checkPasswordChangeRateLimit($user->email);

            // Gửi mã xác thực
            if (!$this->sendPasswordChangeVerification($user)) {
                throw new Exception('Lỗi khi gửi mã xác thực');
            }

            // Lưu thời gian gửi
            Redis::setex("password_change_last_sent:{$user->email}", 600, time());

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi mã xác thực đến email của bạn'
            ]);
        } catch (Exception $e) {
            $this->logAction('Password change verification failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
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
            throw new Exception('Vui lòng đợi 60 giây trước khi gửi lại mã xác thực', 429);
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
