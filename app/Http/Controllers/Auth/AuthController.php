<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Http\Controllers\SuccessMessages;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected AuthService $authService;
    
    public function __construct(AuthService $authService) 
    {
        $this->authService = $authService;
    }

    /**
     * Kiểm tra trạng thái xác thực
     * 
     * @return JsonResponse
     */
    public function checkStatus(): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->authService->checkStatus(),
            null,
            ErrorMessages::AUTH_CHECK_ERROR
        );
    }

    /**
     * Đăng ký người dùng mới
     * 
     * @param SignUpRequest $request
     * @return JsonResponse
     */
    public function register(SignUpRequest $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->authService->register($request),
            SuccessMessages::REGISTER_SUCCESS,
            ErrorMessages::REGISTER_ERROR
        );
    }

    /**
     * Đăng nhập người dùng
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->authService->login($request),
            SuccessMessages::LOGIN_SUCCESS,
            ErrorMessages::LOGIN_ERROR
        );
    }

    /**
     * API Login cho testing (Postman, API clients)
     * Không yêu cầu Turnstile verification
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function apiLogin(Request $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->authService->apiLogin($request),
            SuccessMessages::LOGIN_SUCCESS,
            ErrorMessages::LOGIN_ERROR
        );
    }

    /**
     * Đăng xuất người dùng
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $result = $this->executeServiceMethod(
            fn() => $this->authService->logout($request),
            SuccessMessages::LOGOUT_SUCCESS,
            ErrorMessages::LOGOUT_ERROR
        );

        // Thêm CSRF token cookie vào response khi logout
        if ($result->getStatusCode() === 200) {
            $data = $result->getData(true);
            if (isset($data['data']['csrf_token'])) {
                $result = $result->cookie('XSRF-TOKEN', $data['data']['csrf_token'], 120, null, null, null, false);
            }
        }

        return $result;
    }
}

