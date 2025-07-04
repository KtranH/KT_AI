<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Services\Auth\GoogleService;
use Illuminate\Http\JsonResponse;

class GoogleAuthController extends BaseV1Controller
{
    protected GoogleService $googleService;
    
    public function __construct(GoogleService $googleService) 
    {
        $this->googleService = $googleService;
    }

    /**
     * Lấy URL redirect để chuyển hướng đến Google cho authentication
     * 
     * @return JsonResponse
     */
    public function getRedirectUrl(): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->googleService->getRedirectUrl(),
            SuccessMessages::GOOGLE_REDIRECT_SUCCESS,
            ErrorMessages::GOOGLE_REDIRECT_ERROR
        );
    }

    /**
     * Xử lý callback từ Google
     * 
     * @return string
     */
    public function handleCallback(): string
    {
        try {
            $result = $this->googleService->handleCallback();
            return $this->googleService->createPopupResponse($result);
        } catch (\Exception $e) {
            $errorResult = [
                'success' => false,
                'message' => 'Đăng nhập thất bại',
                'error' => $e->getMessage()
            ];
            return $this->googleService->createPopupResponse($errorResult);
        }
    }
} 