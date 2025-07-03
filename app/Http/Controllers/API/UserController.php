<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Http\Controllers\SuccessMessages;
use App\Http\Requests\User\UpdateNameRequest;
use App\Http\Requests\User\UpdateAvatarRequest;
use App\Http\Requests\User\UpdateCoverImageRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\CheckPasswordRequest;
use App\Services\Auth\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get user profile
     * 
     * @return JsonResponse
     */
    public function getUserProfile(): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->userService->getUserProfile(),
            null,
            ErrorMessages::USER_LOAD_ERROR
        );
    }

    /**
     * Get user by ID
     * 
     * @param mixed $id
     * @return JsonResponse
     */
    public function getUserById($id): JsonResponse
    {
        // Validate ID
        if (!$this->isValidId($id)) {
            return $this->errorResponse(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->userService->getUserById($id),
            null,
            ErrorMessages::USER_LOAD_ERROR
        );
    }

    /**
     * Get user by email
     * 
     * @param string $email
     * @return JsonResponse
     */
    public function getUserByEmail(string $email): JsonResponse
    {
        // Validate email
        if (!$this->isValidEmail($email)) {
            return $this->errorResponse(ErrorMessages::INVALID_EMAIL, 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->userService->getUserByEmail($email),
            null,
            ErrorMessages::USER_LOAD_ERROR
        );
    }

    /**
     * Update user name
     * 
     * @param UpdateNameRequest $request
     * @return JsonResponse
     */
    public function updateName(UpdateNameRequest $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->userService->updateName($request),
            SuccessMessages::SUCCESS_UPDATE,
            ErrorMessages::USER_UPDATE_ERROR
        );
    }

    /**
     * Update user avatar
     * 
     * @param UpdateAvatarRequest $request
     * @return JsonResponse
     */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->userService->updateAvatar($request),
            ErrorMessages::USER_UPDATE_ERROR
        );
    }

    /**
     * Update user cover image
     * 
     * @param UpdateCoverImageRequest $request
     * @return JsonResponse
     */
    public function updateCoverImage(UpdateCoverImageRequest $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->userService->updateCoverImage($request),
            ErrorMessages::USER_UPDATE_ERROR
        );
    }

    /**
     * Update user password
     * 
     * @param UpdatePasswordRequest $request
     * @return JsonResponse
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->userService->updatePassword($request),
            ErrorMessages::USER_UPDATE_ERROR
        );
    }

    /**
     * Check current password
     * 
     * @param CheckPasswordRequest $request
     * @return JsonResponse
     */
    public function checkPassword(CheckPasswordRequest $request): JsonResponse
    {
        return $this->executeServiceJsonMethod(
            fn() => $this->userService->checkPassword($request),
            ErrorMessages::USER_LOAD_ERROR
        );
    }
}
