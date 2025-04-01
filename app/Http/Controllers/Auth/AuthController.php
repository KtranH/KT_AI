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
use App\Repositories\UserRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function __construct(private readonly MailService $mailService, private readonly UserRepository $userRepository) {}
    
    public function checkStatus()
    {
        return $this->userRepository->checkStatus();
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

        $user = $this->userRepository->store($request);

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
        $user = $this->userRepository->getUser();
        
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
            
            // Lấy CSRF token mới
            $token = csrf_token();
            
            // Chuẩn bị response với CSRF token mới
            return response()->json([
                'message' => 'Đã đăng xuất thành công',
                'csrf_token' => $token
            ])->cookie('XSRF-TOKEN', $token, 120, null, null, null, false);
            
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