<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Requests\V1\Auth\Enable2FARequest;
use App\Http\Requests\V1\Auth\Disable2FARequest;
use App\Http\Requests\V1\Auth\Verify2FARequest;
use App\Http\Requests\V1\Auth\RecoveryCodeRequest;
use App\Http\Resources\V1\Auth\Google2FAResource;
use App\Services\Auth\Google2FAService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Google2FAController extends BaseV1Controller
{
    public function __construct(
        private readonly Google2FAService $google2FAService
    ) {}

    /**
     * Lấy trạng thái 2FA của user
     */
    public function getStatus(Request $request): JsonResponse
    {
        try {
            $status = $this->google2FAService->getStatus($request->user());
            
            return $this->successResponse(
                new Google2FAResource($status),
                'Lấy trạng thái 2FA thành công'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi lấy trạng thái 2FA: ' . $e->getMessage()
            );
        }
    }

    /**
     * Tạo mã QR và secret key để setup 2FA
     */
    public function generateQRCode(Request $request): JsonResponse
    {
        try {
            $data = $this->google2FAService->generateQRCode($request->user());
            
            return $this->successResponse($data, 'Tạo mã QR thành công');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi tạo mã QR: ' . $e->getMessage()
            );
        }
    }

    /**
     * Bật 2FA với mã xác thực
     */
    public function enable(Enable2FARequest $request): JsonResponse
    {
        try {
            $result = $this->google2FAService->enable(
                $request->user(),
                $request->validated()
            );
            
            return $this->successResponse($result, 'Bật 2FA thành công');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi bật 2FA: ' . $e->getMessage()
            );
        }
    }

    /**
     * Tắt 2FA với mã xác thực
     */
    public function disable(Disable2FARequest $request): JsonResponse
    {
        try {
            $result = $this->google2FAService->disable(
                $request->user(),
                $request->validated()
            );
            
            return $this->successResponse($result, 'Tắt 2FA thành công');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi tắt 2FA: ' . $e->getMessage()
            );
        }
    }

    /**
     * Tạo mã khôi phục mới
     */
    public function generateRecoveryCodes(Request $request): JsonResponse
    {
        try {
            $codes = $this->google2FAService->generateRecoveryCodes($request->user());
            
            return $this->successResponse($codes, 'Tạo mã khôi phục thành công');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi tạo mã khôi phục: ' . $e->getMessage()
            );
        }
    }

    /**
     * Xác thực 2FA khi đăng nhập
     */
    public function verify(Verify2FARequest $request): JsonResponse
    {
        try {
            $result = $this->google2FAService->verify($request->validated());
            
            return $this->successResponse($result, 'Xác thực 2FA thành công');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi xác thực 2FA: ' . $e->getMessage()
            );
        }
    }

    /**
     * Xác thực 2FA khi đăng nhập và hoàn tất quá trình đăng nhập
     */
    public function verifyLogin2FA(Verify2FARequest $request): JsonResponse
    {
        try {
            $result = $this->google2FAService->verifyLogin2FA($request->validated());
            
            return $this->successResponse($result, 'Đăng nhập thành công');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi xác thực 2FA đăng nhập: ' . $e->getMessage()
            );
        }
    }

    /**
     * Sử dụng mã khôi phục
     */
    public function useRecoveryCode(RecoveryCodeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Kiểm tra xem có challenge_id không để quyết định sử dụng method nào
            if (isset($data['challenge_id'])) {
                $result = $this->google2FAService->useRecoveryCodeWithChallenge($data);
            } else {
                $result = $this->google2FAService->useRecoveryCode($data);
            }
            
            return $this->successResponse($result, 'Sử dụng mã khôi phục thành công');
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Lỗi khi sử dụng mã khôi phục: ' . $e->getMessage()
            );
        }
    }
}
