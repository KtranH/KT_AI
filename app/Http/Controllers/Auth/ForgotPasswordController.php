<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        if (!$this->validateTurnstile($request->input('cf-turnstile-response'))) {
            return response()->json([
                'message' => 'Lỗi xác thực captcha, vui lòng thử lại.'
            ], 422);
        }

        // Tạo mã xác thực 6 số
        $verificationCode = rand(100000, 999999);
        
        // Lưu mã vào database
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();
            
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($verificationCode),
            'created_at' => now()
        ]);

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

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'code' => ['Mã xác thực không hợp lệ hoặc đã hết hạn']
            ]);
        }

        // Kiểm tra thời gian tạo mã (mã chỉ có hiệu lực trong 60 phút)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
                
            throw ValidationException::withMessages([
                'code' => ['Mã xác thực đã hết hạn. Vui lòng yêu cầu gửi lại mã mới.']
            ]);
        }

        // Kiểm tra mã xác thực
        if (!Hash::check($request->code, $record->token)) {
            throw ValidationException::withMessages([
                'code' => ['Mã xác thực không chính xác.']
            ]);
        }

        // Tạo token mới để sử dụng cho bước reset mật khẩu
        $token = Str::random(64);
        
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->update([
                'token' => $token,
                'updated_at' => now()
            ]);

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

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'email' => ['Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.']
            ]);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Không tìm thấy tài khoản với email này.']
            ]);
        }

        // Cập nhật mật khẩu
        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        // Xóa token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Kích hoạt sự kiện password reset
        event(new PasswordReset($user));

        return response()->json([
            'message' => 'Mật khẩu đã được đặt lại thành công'
        ]);
    }

    /**
     * Xác thực Turnstile (Cloudflare)
     *
     * @param string $token
     * @return bool
     */
    private function validateTurnstile(string $token): bool
    {
        $secretKey = config('services.turnstile.secret_key');
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        
        $data = [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => request()->ip(),
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response);

        return $result->success ?? false;
    }
} 