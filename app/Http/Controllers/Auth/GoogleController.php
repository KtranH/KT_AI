<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleService;
use App\Http\Controllers\ErrorMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    protected GoogleService $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    /**
     * Tạo URL redirect cho Google OAuth
     *
     * @return JsonResponse
     */
    public function redirectUrl(): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->googleService->getRedirectUrl(),
            null,
            ErrorMessages::GOOGLE_REDIRECT_ERROR
        );
    }

    /**
     * Xử lý callback từ Google OAuth
     *
     * @param Request $request
     * @return string
     */
    public function handleCallback(Request $request): string
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