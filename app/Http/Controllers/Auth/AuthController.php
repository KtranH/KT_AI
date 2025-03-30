<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Services\MailService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Mail\MailRequest;

class AuthController extends Controller
{
    public function __construct(private readonly MailService $mailService, 
                                private readonly LoginRequest $loginRequest, 
                                private readonly RegisterRequest $registerRequest, 
                                private readonly MailRequest $mailRequest) {}
    
    public function check()
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user()
        ]);
    }

    public function register(Request $request)
    {
        $request->validate($this->registerRequest->rules());

        // Xác thực Turnstile
        $turnstileResponse = $this->verifyTurnstile($request->input('cf-turnstile-response'), $request->ip());
        if (!$turnstileResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Xác thực không thành công. Vui lòng thử lại.'
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png",
            'cover_image_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png",
            'remaining_creadits' => 0,
            'sum_img' => 0,
            'sum_like' => 0,
            'status_user' => 'active',
            'is_verified' => false
        ]);

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
        $request->validate($this->mailRequest->rules());

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

        $user = User::where('email', $request->email)->first();
        
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
        $request->validate($this->loginRequest->rules());

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
        $user = User::with(['images', 'comments', 'notifications', 'interactions'])->find(Auth::user()->id);
        
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
        $user = $request->user();
        if (method_exists($user, 'tokens')) {
            // Nếu dùng token-based, xoá toàn bộ token
            $user->tokens()->delete();
        }

        // Nếu dùng session-based (Google OAuth), logout session
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Xóa dữ liệu user trong session
        $request->session()->forget('user');

        return response()->json(['message' => 'Đã đăng xuất thành công']);
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