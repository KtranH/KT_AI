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

class AuthService extends BaseService
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
                \Log::warning('❌ Turnstile verification failed', [
                    'email' => $request->input('email'),
                    'ip' => $request->ip()
                ]);
                throw ValidationException::withMessages([
                    'captcha' => ['Xác thực không thành công. Vui lòng thử lại.'],
                ]);
            }

            // 2. Xác thực tài khoản
            if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                \Log::warning('❌ Credential authentication failed', [
                    'email' => $request->input('email'),
                    'ip' => $request->ip()
                ]);
                throw ValidationException::withMessages([
                    'email' => ['Thông tin đăng nhập không chính xác.'],
                ]);
            }
            
            // Lấy user mới nhất từ database
            $user = $this->userRepository->getUser();

            if (!$user->is_verified) {
                Auth::logout();
                throw BusinessException::invalidPermissions('unverified email');
            }

            // 3. Sanctum Token Generation
            $tokenName = $request->boolean('remember') ? 'auth_token_remember' : 'auth_token_session';
            $token = $user->createToken($tokenName)->plainTextToken;

            return new AuthResource(
                $user, 
                $token, 
                'Bearer', 
                $request->boolean('remember'),
                null,
                'Đăng nhập thành công với bảo mật 2 lớp'
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
            if ($user && method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            
            return AuthResource::logout('Đăng xuất thành công');
        }, "Logging out user ID: " . ($request->user()->id ?? 'unknown'));
    }
}