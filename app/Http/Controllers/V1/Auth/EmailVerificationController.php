<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Services\Business\MailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends BaseV1Controller
{
    protected MailService $mailService;
    
    public function __construct(MailService $mailService) 
    {
        $this->mailService = $mailService;
    }

    /**
     * Xác thực email với mã verification
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->mailService->verifyEmail($request),
            SuccessMessages::EMAIL_VERIFIED_SUCCESS,
            ErrorMessages::MAIL_VERIFY_ERROR
        );
    }

    /**
     * Gửi lại mã xác thực email
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerificationCode(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->mailService->resendVerification($request),
            SuccessMessages::MAIL_RESEND_SUCCESS,
            ErrorMessages::MAIL_RESEND_ERROR
        );
    }
} 