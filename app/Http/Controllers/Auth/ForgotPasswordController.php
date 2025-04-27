<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ForgotPasswordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        try
        {
            return $this->forgotPasswordService->sendResetLinkEmail($request);
        }
        catch (ValidationException $e)
        {
            return $this->handleValidationException($e);
        }
    }

    /**
     * Xác thực mã xác nhận qua email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyCode(Request $request): JsonResponse
    {
        try
        {
            return $this->forgotPasswordService->verifyCode($request);
        }
        catch (ValidationException $e)
        {
            return $this->handleValidationException($e);
        }
    }

    /**
     * Đặt lại mật khẩu
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        try
        {
            return $this->forgotPasswordService->reset($request);
        }
        catch (ValidationException $e)
        {
            return $this->handleValidationException($e);
        }
    }

    /**
     * Handle validation exception
     *
     * @param ValidationException $e
     * @return JsonResponse
     */
    private function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => $e->errors()
        ], 422);
    }
}