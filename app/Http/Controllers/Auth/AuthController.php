<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignUpRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * The authentication service instance.
     */
    protected AuthService $authService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Check authentication status.
     */
    public function checkStatus(): JsonResponse
    {
        try {
            return $this->authService->checkStatus();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra trạng thái xác thực',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new user.
     */
    public function register(SignUpRequest $request): JsonResponse
    {
        try {
            // Tạo request mới với dữ liệu đã được validate
            $validatedRequest = new Request($request->validated());
            $validatedRequest->merge([
                'cf-turnstile-response' => $request->input('cf-turnstile-response')
            ]);

            return $this->authService->register($validatedRequest, $request->input('cf-turnstile-response'), $request->ip());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đăng ký tài khoản',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate a user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            return $this->authService->login($request);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đăng nhập',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            return $this->authService->logout($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đăng xuất',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

