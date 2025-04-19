<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers\API\TurnstileController;
use App\Services\TurnStileService;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Redis;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly TurnStileService $turnStileService,
        private readonly UserRepository $userRepository
    ) {}
    /**
     * Gửi email chứa link khôi phục mật khẩu
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'cf-turnstile-response' => 'required|string'
        ]);

        // Xác thực Turnstile
        $turnstileResponse = $this->turnStileService->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip());

        
        if (!$turnstileResponse['success']) {
            throw ValidationException::withMessages([
                'captcha' => ['Xác thực không thành công. Vui lòng thử lại.'],
            ]);
        }

        // Lưu biến đếm số lần gửi mã
        $emailKey = "password_reset_code_send_count:{$request->email}";
        $attempts = Redis::incr($emailKey);
        if ($attempts === 1) {
            Redis::expire($emailKey, 300); // 5 phút
        }

        // Kiểm tra biến đếm có vượt quá số lần chưa
        if ($attempts > 3) {
            $ttl = Redis::ttl($emailKey);
    
            throw ValidationException::withMessages([
                'email' => ["Bạn đã yêu cầu quá số lần cho phép. Vui lòng thử lại sau {$ttl} giây."]
            ]);
        }

        // Tạo mã xác thực 6 số
        $verificationCode = rand(100000, 999999);
        
        // Lưu mã vào Redis
        Redis::setex("password_reset_code:{$request->email}", 600, Hash::make($verificationCode));
        // Lưu trữ thời gian tạo mã
        Redis::setex("password_reset_code_created_at:{$request->email}", 600, time());

        // Gửi email chứa mã xác thực
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            $user->sendPasswordResetNotification($verificationCode);
            
            return response()->json([
                'message' => 'Chúng tôi đã gửi mã xác nhận đến email của bạn.'
            ]);
        }

        return response()->json([
            'message' => 'Chúng tôi đã gửi mã xác nhận đến email của bạn nếu tài khoản tồn tại.'
        ]);
    }

    /**
     * Xác thực mã xác nhận qua email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $record = Redis::get("password_reset_code:{$request->email}");
        
        // Lấy thời gian tạo mã lần cuối
        $createdAtTimeStamp = Redis::get("password_reset_code_created_at:{$request->email}");

        if (!$record) {
            throw ValidationException::withMessages([
                'code' => ['Mã xác thực không hợp lệ hoặc đã hết hạn']
            ]);
        }

        // Kiểm tra thời gian tạo mã (mã chỉ có hiệu lực trong 60 phút)
        if ($createdAtTimeStamp && (time() - $createdAtTimeStamp) > 60) {
            Redis::del("password_reset_code:{$request->email}");
            
            throw ValidationException::withMessages([
                'code' => ['Mã xác thực đã hết hạn. Vui lòng yêu cầu gửi lại mã mới.']
            ]);
        }

        // Kiểm tra mã xác thực
        if (!Hash::check($request->code, $record)) {
            throw ValidationException::withMessages([
                'code' => ['Mã xác thực không chính xác.']
            ]);
        }

        // Tạo token mới để sử dụng cho bước reset mật khẩu
        $token = Str::random(64);
        
        Redis::setex("password_reset_token:{$request->email}", 600, $token);

        return response()->json([
            'message' => 'Mã xác thực hợp lệ',
            'token' => $token
        ]);
    }

    /**
     * Đặt lại mật khẩu
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', PasswordRules::defaults()],
        ]);

        $record = Redis::get("password_reset_token:{$request->email}");

        if (!$record) {
            throw ValidationException::withMessages([
                'email' => ['Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']
            ]);
        }

        $user = $this->userRepository->getUserByEmail($request->email);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Không tìm thấy tài khoản với email này.']
            ]);
        }

        // Cập nhật mật khẩu
        

        // Xóa token
        Redis::del("password_reset_token:{$request->email}");
        // Xóa thời gian tạo lần cuối
        Redis::del("password_reset_code_created_at:{$request->email}");

        // Kích hoạt sự kiện password reset
        event(new PasswordReset($user));

        return response()->json([
            'message' => 'Mật khẩu đã được đặt lại thành công'
        ]);
    }
} 