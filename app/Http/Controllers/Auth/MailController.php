<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use App\Http\Controllers\ErrorMessages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MailController extends Controller
{
    protected MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    /**
     * Xác thực email với mã xác nhận
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->mailService->verifyEmail($request),
            ErrorMessages::MAIL_VERIFY_ERROR
        );
    }

    /**
     * Gửi lại mã xác thực email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerification(Request $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->mailService->resendVerification($request),
            ErrorMessages::MAIL_RESEND_ERROR
        );
    }

    /**
     * Gửi mã xác thực đổi mật khẩu
     *
     * @return JsonResponse
     */
    public function sendPasswordChangeVerification(): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->mailService->sendPasswordChangeVerificationCode(),
            ErrorMessages::MAIL_SEND_PASSWORD_CHANGE_VERIFICATION_ERROR
        );
    }
}
