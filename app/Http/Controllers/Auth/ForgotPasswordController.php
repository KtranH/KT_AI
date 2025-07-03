<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Services\Auth\ForgotPasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /**
     * The ForgotPasswordService instance.
     *
     * @var ForgotPasswordService
     */
    protected ForgotPasswordService $forgotPasswordService;

    /**
     * Create a new controller instance.
     *
     * @param ForgotPasswordService $forgotPasswordService
     * @return void
     */
    public function __construct(ForgotPasswordService $forgotPasswordService)
    {
        $this->forgotPasswordService = $forgotPasswordService;
    }

    /**
     * Gửi email chứa mã xác nhận khôi phục mật khẩu
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->forgotPasswordService->sendResetLinkEmail($request),
            ErrorMessages::FORGOT_PASSWORD_SEND_ERROR
        );
    }

    /**
     * Xác thực mã xác nhận qua email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCode(Request $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->forgotPasswordService->verifyCode($request),
            ErrorMessages::FORGOT_PASSWORD_VERIFY_ERROR
        );
    }

    /**
     * Đặt lại mật khẩu
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->forgotPasswordService->reset($request),
            ErrorMessages::FORGOT_PASSWORD_RESET_ERROR
        );
    }
}