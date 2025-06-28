<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailService;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MailController extends Controller
{
    public function __construct(private readonly MailService $mailService, private readonly UserRepositoryInterface $userRepository) {}
    public function verifyEmail(Request $request)
    {
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

        // Cập nhật trạng thái user
        // Sử dụng Auth::login để đăng nhập user trước khi cập nhật trạng thái
        Auth::login($user);
        $this->userRepository->updateStatus();

        Redis::del("verify_code:{$request->email}");
        Redis::del("verify_attempts:{$request->email}");
        Redis::del("verify_last_attempt:{$request->email}");

        return response()->json([
            'message' => 'Xác thực email thành công.',
            'user' => $user
        ]);
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Lưu biến đếm số lần gửi lại mã
        $resendCount = Redis::get("verify_resend_count:{$request->email}") ?: 0;
        if($resendCount === 1) {
            Redis::expire("verify_resend_count:{$request->email}", 300); // 5 phút
        }

        // Kiểm tra có vượt số lần gửi chưa
        if ($resendCount > 3) {
            $ttl = Redis::ttl("verify_resend_count:{$request->email}");
            throw ValidationException::withMessages([
                'email' => ["Bạn đã vượt quá số lần gửi lại mã cho phép. Vui lòng thử lại sau {$ttl} giây."]
            ]);
        }

        $lastResendTime = Redis::get("verify_last_resend:{$request->email}");

        if ($lastResendTime && (time() - $lastResendTime) < 60) {
            throw ValidationException::withMessages([
                'email' => ['Vui lòng đợi 60 giây trước khi gửi lại mã.'],
            ]);
        }

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

        $verificationCode = Str::random(6);
        Redis::setex("verify_code:{$request->email}", 600, $verificationCode);
        Redis::incr("verify_resend_count:{$request->email}");
        Redis::setex("verify_last_resend:{$request->email}", 600, time());

        if (!$this->mailService->sendMailResend($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi mã xác thực'
            ], 500);
        }

        return response()->json([
            'message' => 'Đã gửi lại mã xác thực.',
        ]);
    }
    // Gửi mã xác thực đổi mật khẩu
    public function sendPasswordChangeVerification() {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Người dùng chưa đăng nhập'
                ], 401);
            }

            // Kiểm tra xem đã gửi mã xác thực gần đây chưa
            $lastSentTime = Redis::get("password_change_last_sent:{$user->email}");
            if ($lastSentTime && (time() - $lastSentTime) < 60) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đợi 60 giây trước khi gửi lại mã xác thực'
                ], 429);
            }

            // Gửi mã xác thực qua email
            if (!$this->mailService->sendPasswordChangeVerification($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi gửi mã xác thực'
                ], 500);
            }

            // Lưu thời gian gửi mã xác thực
            Redis::setex("password_change_last_sent:{$user->email}", 600, time());

            return response()->json([
                'success' => true,
                'message' => 'Đã gửi mã xác thực đến email của bạn'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi mã xác thực',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
