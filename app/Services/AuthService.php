<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;
use App\Services\TurnStileService;
use App\Services\MailService;


class AuthService
{
    protected MailService $mailService;
    protected UserRepository $userRepository;
    protected TurnStileService $turnStileService;
    
    public function __construct(UserRepository $userRepository, TurnStileService $turnStileService, MailService $mailService) 
    {
        $this->userRepository = $userRepository;
        $this->turnStileService = $turnStileService;
        $this->mailService = $mailService;
    }
    
    /**
     * Kiểm tra trạng thái của user
     * 
     * @return array
     */
    public function checkStatus(): array
    {
        return $this->userRepository->checkStatus();
    }
    
    /**
     * Đăng ký user
     * 
     * @param Request $request
     * @return array
     */
    public function register(Request $request): array
    {
        // Xác thực Turnstile
        $turnstileResponse = $this->turnStileService->verifyTurnstile(
            $request->input('cf-turnstile-response'), 
            $request->ip()
        );

        if (!$turnstileResponse['success']) {
            throw new \Exception('Xác thực không thành công. Vui lòng thử lại.');
        }

        $user = $this->userRepository->store($request->all());
        
        if (!$this->mailService->sendMail($user)) {
            throw new \Exception('Lỗi khi gửi mã xác thực');
        }
        
        return [
            'user_id' => $user->id,
            'email' => $user->email,
            'verification_sent' => true
        ];
    }
    
    /**
     * Đăng nhập user
     * 
     * @param Request $request
     * @return array
     */
    public function login(Request $request): array
    {
        // Xác thực Turnstile
        $turnstileResponse = $this->turnStileService->verifyTurnstile(
            $request->input('cf-turnstile-response'), 
            $request->ip()
        );

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
            throw new \Exception('Vui lòng xác thực email trước khi đăng nhập.', 403);
        }

        // Tạo token Sanctum với thời hạn phù hợp với lựa chọn remember_me
        $tokenName = 'auth_token';

        if ($request->boolean('remember')) {
            $tokenName = 'auth_token_remember';
        } else {
            $tokenName = 'auth_token_session';
        }

        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'remember' => $request->boolean('remember')
        ];
    }

    /**
     * API Login cho testing (Postman, API clients)
     * Không yêu cầu Turnstile verification
     * 
     * @param Request $request
     * @return array
     */
    public function apiLogin(Request $request): array
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean'
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw new \Exception('Thông tin đăng nhập không chính xác.', 401);
        }

        // Lấy user mới nhất từ database
        $user = $this->userRepository->getUser();

        if (!$user->is_verified) {
            Auth::logout();
            throw new \Exception('Vui lòng xác thực email trước khi đăng nhập.', 403);
        }

        // Xóa các token cũ (tuỳ chọn)
        $user->tokens()->delete();

        // Tạo token mới
        $tokenName = $request->boolean('remember') ? 'api_token_remember' : 'api_token';
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user->only('id', 'name', 'email', 'is_verified', 'created_at', 'updated_at'),
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60 // Convert minutes to seconds
        ];
    }

    /**
     * Đăng xuất user
     * 
     * @param Request $request
     * @return array
     */
    public function logout(Request $request): array
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

            return [
                'csrf_token' => $token,
                'logged_out' => true
            ];

        } catch (\Exception $e) {
            report($e);
            throw new \Exception('Đã xảy ra lỗi khi đăng xuất: ' . $e->getMessage());
        }
    }
}