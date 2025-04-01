<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use App\Services\MailService;
use App\Interfaces\UserRepositoryInterface;


class MailController extends Controller
{
    public function __construct(private readonly MailService $mailService, private readonly UserRepository $userRepository) {}
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
        $this->userRepository->updateUserStatus($user->id);

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

        $resendCount = Redis::get("verify_resend_count:{$request->email}") ?: 0;
        
        if ($resendCount >= 2) {
            throw ValidationException::withMessages([
                'email' => ['Bạn đã vượt quá số lần gửi lại mã cho phép.'],
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
}
