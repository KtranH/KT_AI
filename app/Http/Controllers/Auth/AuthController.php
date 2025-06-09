<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SignUpRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;
    public function __construct(AuthService $authService) 
    {
        $this->authService = $authService;
    }

    public function checkStatus()
    {
        try {
            return $this->authService->checkStatus();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi kiểm tra trạng thái xác thực',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(SignUpRequest $request)
    {
        return $this->authService->register($request->validated());
    }

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request->validated());
    }

    public function logout(Request $request)
    {
        return $this->authService->logout($request);
    }

}

