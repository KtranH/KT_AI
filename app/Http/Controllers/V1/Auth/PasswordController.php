<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Services\Auth\ForgotPasswordService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PasswordController extends BaseV1Controller
{
    protected ForgotPasswordService $forgotPasswordService;
    
    public function __construct(ForgotPasswordService $forgotPasswordService) 
    {
        $this->forgotPasswordService = $forgotPasswordService;
    }

    /**
     * Gửi email reset password
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->forgotPasswordService->sendResetLinkEmail($request),
            SuccessMessages::MAIL_SEND_PASSWORD_CHANGE_SUCCESS,
            ErrorMessages::FORGOT_PASSWORD_SEND_ERROR
        );
    }

    /**
     * Xác thực mã reset password
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCode(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->forgotPasswordService->verifyCode($request),
            null,
            ErrorMessages::FORGOT_PASSWORD_VERIFY_ERROR
        );
    }

    /**
     * Reset password với mã xác thực
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->forgotPasswordService->reset($request),
            SuccessMessages::PASSWORD_RESET_SUCCESS,
            ErrorMessages::FORGOT_PASSWORD_RESET_ERROR
        );
    }
} 