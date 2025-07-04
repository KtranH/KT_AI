<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\User;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Http\Requests\V1\User\UpdateNameRequest;
use App\Http\Requests\V1\User\UpdateAvatarRequest;
use App\Http\Requests\V1\User\UpdateCoverImageRequest;
use App\Http\Requests\V1\User\UpdatePasswordRequest;
use App\Http\Requests\V1\User\CheckPasswordRequest;
use App\Services\Auth\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileController extends BaseV1Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Lấy thông tin profile của user hiện tại
     * 
     * @return JsonResponse
     */
    public function getProfile(): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->userService->getUserProfile(),
            null,
            ErrorMessages::USER_LOAD_ERROR
        );
    }

    /**
     * Lấy thông tin user theo ID (public profile)
     * 
     * @param mixed $id
     * @return JsonResponse
     */
    public function getUserById($id): JsonResponse
    {
        if (!$this->isValidId($id)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->userService->getUserById($id),
            null,
            ErrorMessages::USER_LOAD_ERROR
        );
    }

    /**
     * Lấy thông tin user theo email
     * 
     * @param string $email
     * @return JsonResponse
     */
    public function getUserByEmail(string $email): JsonResponse
    {
        if (!$this->isValidEmail($email)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_EMAIL, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->userService->getUserByEmail($email),
            null,
            ErrorMessages::USER_LOAD_ERROR
        );
    }

    /**
     * Cập nhật tên user
     * 
     * @param UpdateNameRequest $request
     * @return JsonResponse
     */
    public function updateName(UpdateNameRequest $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->userService->updateName($request),
            SuccessMessages::SUCCESS_UPDATE,
            ErrorMessages::USER_UPDATE_ERROR
        );
    }

    /**
     * Cập nhật avatar user
     * 
     * @param UpdateAvatarRequest $request
     * @return JsonResponse
     */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $response = $this->executeServiceMethod(
            fn() => $this->userService->updateAvatar($request),
            null,
            ErrorMessages::USER_UPDATE_ERROR
        );
        
        return $this->withVersionHeader($response);
    }

    /**
     * Cập nhật cover image user
     * 
     * @param UpdateCoverImageRequest $request
     * @return JsonResponse
     */
    public function updateCoverImage(UpdateCoverImageRequest $request): JsonResponse
    {
        $response = $this->executeServiceMethod(
            fn() => $this->userService->updateCoverImage($request),
            null,
            ErrorMessages::USER_UPDATE_ERROR
        );
        
        return $this->withVersionHeader($response);
    }

    /**
     * Cập nhật password user
     * 
     * @param UpdatePasswordRequest $request
     * @return JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $response = $this->executeServiceMethod(
            fn() => $this->userService->updatePassword($request),
            null,
            ErrorMessages::USER_UPDATE_ERROR
        );
        
        return $this->withVersionHeader($response);
    }

    /**
     * Kiểm tra password hiện tại
     * 
     * @param CheckPasswordRequest $request
     * @return JsonResponse
     */
    public function checkPassword(CheckPasswordRequest $request): JsonResponse
    {
        $response = $this->executeServiceMethod(
            fn() => $this->userService->checkPassword($request),
            null,
            ErrorMessages::USER_LOAD_ERROR
        );
        
        return $this->withVersionHeader($response);
    }
} 