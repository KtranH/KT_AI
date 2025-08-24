<?php

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use App\Exceptions\ExternalServiceException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;
use App\Services\Auth\TurnStileService;
use App\Services\External\MailService;
use App\Services\BaseService;
use App\Http\Requests\V1\Auth\PostmanRequest;
use App\Http\Requests\V1\Auth\SignUpRequest;
use App\Http\Requests\V1\Auth\LoginRequest;
use App\Http\Resources\V1\Auth\AuthResource;
use App\Services\Auth\Google2FAService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthService extends BaseService
{
    protected MailService $mailService;
    protected UserRepository $userRepository;
    protected TurnStileService $turnStileService;
    protected Google2FAService $google2FAService;
    
    public function __construct(UserRepository $userRepository, TurnStileService $turnStileService, MailService $mailService, Google2FAService $google2FAService) 
    {
        $this->userRepository = $userRepository;
        $this->turnStileService = $turnStileService;
        $this->mailService = $mailService;
        $this->google2FAService = $google2FAService;
    }
    
    /**
     * Kiểm tra trạng thái của user
     * 
     * @return array
     */
    public function checkStatus(): array
    {
        // Ưu tiên session (web)
        $sessionAuth = $this->userRepository->checkStatus();
        if ($sessionAuth['authenticated'] === true) {
            return $sessionAuth;
        }

        // Fallback: Bearer token (mobile/api client) không qua middleware
        try {
            $request = request();
            $bearer = $request->bearerToken();
            if ($bearer) {
                \Laravel\Sanctum\PersonalAccessToken::preventAccessingMissingColumns();
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                if ($tokenModel && $tokenModel->tokenable) {
                    return [
                        'authenticated' => true,
                        'user' => $tokenModel->tokenable,
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Silent fallback
        }

        return [
            'authenticated' => false,
            'user' => null,
        ];
    }
    
    /**
     * Đăng ký user
     * 
     * @param SignUpRequest $request
     * @return AuthResource
     */
    public function register(SignUpRequest $request): AuthResource
    {
        return $this->executeInTransactionSafely(function() use ($request) {
            // Validate input
            $request->validated();
            // Xác thực Turnstile
            $turnstileResponse = $this->turnStileService->verifyTurnstile(
                $request->input('cf-turnstile-response'), 
                $request->ip()
            );

            if (!$turnstileResponse['success']) {
                throw new ExternalServiceException(
                    'Lỗi xác thực Turnstile: Xác thực không thành công',
                    ['service' => 'Turnstile', 'ip' => $request->ip()]
                );
            }

            $user = $this->userRepository->store($request);
            
            if (!$this->mailService->sendMail($user)) {
                throw ExternalServiceException::emailServiceError('Failed to send verification email');
            }
            
            return AuthResource::registration($user, 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.');
        }, "Registering user with email: {$request->input('email')}");
    }
    
    /**
     * Đăng nhập user với Double Protection (CSRF + Sanctum + Turnstile)
     * 
     * @param LoginRequest $request
     * @return AuthResource
     */
    public function login(LoginRequest $request): AuthResource
    {
        return $this->executeWithExceptionHandling(function() use ($request) {    
            // Validate input
            $request->validated();

            // 1. Turnstile Protection (Anti-bot)
            $turnstileResponse = $this->turnStileService->verifyTurnstile(
                $request->input('cf-turnstile-response'), 
                $request->ip()
            );

            if (!$turnstileResponse['success']) {
                throw ValidationException::withMessages([
                    'captcha' => ['Xác thực không thành công. Vui lòng thử lại.'],
                ]);
            }

            // 2. Xác thực tài khoản và tạo session
            if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'email' => ['Thông tin đăng nhập không chính xác.'],
                ]);
            }
            
            // Lấy user từ session đã được tạo bởi Auth::attempt()
            $user = Auth::user();
            
            if (!$user || !$user->is_verified) {
                Auth::logout();
                throw BusinessException::invalidPermissions('unverified email');
            }
            
            // 3. Kiểm tra tài khoản có kích hoạt 2FA không
            if ($this->google2FAService->isEnabled($user)) {
                // Xóa session nếu 2FA được bật
                Auth::logout();
                
                // Tạo challenge ID để theo dõi quá trình xác thực 2FA
                $challengeId = uniqid('2fa_challenge_', true);
                
                // Lưu challenge vào cache với thời gian hết hạn (5 phút)
                Cache::put($challengeId, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'remember' => $request->boolean('remember'),
                    'created_at' => now(),
                    'ip' => $request->ip()
                ], now()->addMinutes(5));
                
                // Trả về thông tin cần thiết để frontend hiển thị form 2FA
                return new AuthResource(
                    $user,
                    null,
                    '2FA_Required',
                    $request->boolean('remember'),
                    null,
                    'Vui lòng xác thực 2FA để đăng nhập',
                    $challengeId
                );
            }
            
            // Đảm bảo session được lưu và regenerate để tăng bảo mật
            $request->session()->regenerate();

            // 4. Web session-based login: Session đã được tạo bởi Auth::attempt()
            // Không cần phát token, rely vào cookie session + CSRF
            return new AuthResource(
                $user,
                null,
                'Session',
                $request->boolean('remember'),
                null,
                'Đăng nhập thành công (session cookie)'
            );
        }, "Login attempt for email: {$request->input('email')}");
    }

    /**
     * API Login cho testing (Postman, API clients)
     * Không yêu cầu Turnstile verification
     * 
     * @param PostmanRequest $request
     * @return AuthResource
     */
    public function apiLogin(PostmanRequest $request): AuthResource
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            $request->validated();
            if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                throw BusinessException::invalidPermissions('incorrect credentials');
            }

            // Lấy user mới nhất từ database
            $user = $this->userRepository->getUser();

            if (!$user->is_verified) {
                Auth::logout();
                throw BusinessException::invalidPermissions('unverified email');
            }

            // Xóa các token cũ (tuỳ chọn)
            $user->tokens()->delete();

            // Tạo token mới
            $tokenName = $request->boolean('remember') ? 'api_token_remember' : 'api_token';
            $token = $user->createToken($tokenName)->plainTextToken;

            return new AuthResource(
                $user,
                $token,
                'Bearer',
                $request->boolean('remember'),
                config('sanctum.expiration') * 60, // Convert minutes to seconds
                'API đăng nhập thành công'
            );
        }, "API login attempt for email: {$request->input('email')}");
    }

    /**
     * Đăng xuất user
     * 
     * @param Request $request Request object
     * @return AuthResource
     */
    public function logout(Request $request): AuthResource
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            $user = $request->user();
            // Xóa PAT cho mobile nếu có
            if ($user && method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // Đăng xuất session cho web - Sử dụng guard cụ thể
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
                // Vô hiệu hóa session hiện tại
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Lấy CSRF token mới (để client có thể cập nhật nếu muốn)
            $csrfToken = csrf_token();

            return AuthResource::logout('Đăng xuất thành công', $csrfToken);
        }, "Logging out user ID: " . ($request->user()->id ?? 'unknown'));
    }
}