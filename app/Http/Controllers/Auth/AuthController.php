<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use App\Services\MailService;
use App\Services\UserService;

class AuthController extends Controller
{
    public function __construct(private readonly MailService $mailService, 
                                private readonly UserService $userService) {}
    
    public function check()
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user()
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'cf-turnstile-response' => ['required', 'string'],
        ]);

        // Xác thực Turnstile
        $turnstileResponse = $this->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip());
        if (!$turnstileResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Xác thực không thành công. Vui lòng thử lại.'
            ], 400);
        }

        $user = $this->userService->createUser($request);

        if (!$this->mailService->sendMail($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi mã xác thực'
            ], 500);
        }
        return response()->json([
            'success' => true,
            'message' => 'Đã gửi mã xác thực'],
            200);
    }

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

        $user = $this->userService->checkEmail($request->email);
        
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Không tìm thấy tài khoản.'],
            ]);
        }

        $user->is_verified = true;
        $user->save();

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

        $user = User::where('email', $request->email)->first();
        
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

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => ['required', 'string'],
        ]);

        // Xác thực Turnstile
        $turnstileResponse = $this->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip());
        if (!$turnstileResponse['success']) {
            throw ValidationException::withMessages([
                'captcha' => ['Xác thực không thành công. Vui lòng thử lại.'],
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        // Lấy user mới nhất từ database
        $user = $this->userService->getUser();
        
        if (!$user->is_verified) {
            Auth::logout();
            return response()->json([
                'message' => 'Vui lòng xác thực email trước khi đăng nhập.',
                'needs_verification' => true,
                'email' => $user->email
            ], 403);
        }

        // Tạo token Sanctum với thời hạn phù hợp với lựa chọn remember_me
        $tokenName = 'auth_token';
        $tokenExpiration = null;
        
        if ($request->boolean('remember')) {
            // Nếu người dùng chọn remember_me, token sẽ hết hạn sau 7 ngày (được cấu hình trong sanctum.php)
            $tokenName = 'auth_token_remember';
        } else {
            // Nếu không chọn remember_me, token sẽ hết hạn khi đóng trình duyệt (session->expire_on_close = true)
            $tokenName = 'auth_token_session';
        }
        
        $token = $user->createToken($tokenName)->plainTextToken;
        
        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token,
            'remember' => $request->boolean('remember')
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if ($user && method_exists($user, 'tokens')) {
                // Nếu dùng token-based, xoá toàn bộ token
                $user->tokens()->delete();
            }

            // Nếu dùng session-based (Google OAuth), logout session
            Auth::guard('web')->logout();
            
            // Đảm bảo regenerate session
            if ($request->session()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Xóa dữ liệu user trong session
            if ($request->session()) {
                $request->session()->forget('user');
            }
            
            // Clear cookies liên quan đến OAuth nếu có
            $cookieNames = ['laravel_session', 'XSRF-TOKEN'];
            $response = response()->json(['message' => 'Đã đăng xuất thành công']);
            
            foreach ($cookieNames as $cookieName) {
                $response->withoutCookie($cookieName);
            }
            
            return $response;
        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Đã xảy ra lỗi khi đăng xuất: ' . $e->getMessage()], 500);
        }
    }

    /*public function user(Request $request)
    {
        return response()->json(Auth::user());
    }*/

    // Thêm phương thức mới để xác thực Turnstile
    private function verifyTurnstile($token, $remoteip)
    {
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => env('TURNSTILE_SECRET_KEY'),
            'response' => $token,
            'remoteip' => $remoteip,
        ]);

        return $response->json();
    }
} 