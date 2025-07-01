<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\ExternalServiceException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Repositories\UserRepository;
use App\Services\TurnStileService;
use App\Services\MailService;
use App\Http\Requests\Auth\PostmanRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthResource;

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
     * @param Request $request
     * @return array
     */
    public function register(SignUpRequest $request): array
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

            $user = $this->userRepository->store($request->all());
            
            if (!$this->mailService->sendMail($user)) {
                throw ExternalServiceException::emailServiceError('Failed to send verification email');
            }
            
            return AuthResource::registration($user, 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.');
        }, "Registering user with email: {$request->input('email')}");
    }
    
    /**
     * Đăng nhập user
     * 
     * @param Request $request
     * @return array
     */
    public function login(LoginRequest $request): array
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            // Validate input
            $request->validated();

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
                throw BusinessException::invalidPermissions('unverified email');
            }

            // Tạo token Sanctum với thời hạn phù hợp với lựa chọn remember_me
            $tokenName = 'auth_token';

            if ($request->boolean('remember')) {
                $tokenName = 'auth_token_remember';
            } else {
                $tokenName = 'auth_token_session';
            }

            $token = $user->createToken($tokenName)->plainTextToken;

            return new AuthResource(
                $user, 
                $token, 
                'Bearer', 
                $request->boolean('remember'),
                null,
                'Đăng nhập thành công'
            );
        }, "Login attempt for email: {$request->input('email')}");
    }

    /**
     * API Login cho testing (Postman, API clients)
     * Không yêu cầu Turnstile verification
     * 
     * @param Request $request
     * @return array
     */
    public function apiLogin(PostmanRequest $request): array
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
     * @param Request $request
     * @return array
     */
    public function logout(Request $request): array
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