<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Social;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Services\Business\LikeService;
use Illuminate\Http\JsonResponse;

class LikeController extends BaseV1Controller
{
    protected LikeService $likeService;
    
    public function __construct(LikeService $likeService) 
    {
        $this->likeService = $likeService;
    }
    
    /**
     * Kiểm tra xem một image đã được like hay chưa
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function checkLiked(int $id): JsonResponse
    {
        if (!$this->isValidId($id)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->likeService->checkLiked($id),
            null,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
    
    /**
     * Lấy danh sách likes của một image
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getLikes(int $id): JsonResponse
    {
        if (!$this->isValidId($id)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->likeService->getLikes($id),
            null,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
    
    /**
     * Like một image
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function likePost(int $id): JsonResponse
    {
        if (!$this->isValidId($id)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->likeService->likePost($id),
            SuccessMessages::LIKE_TOGGLE_SUCCESS,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
    
    /**
     * Unlike một image
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function unlikePost(int $id): JsonResponse
    {
        if (!$this->isValidId($id)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->likeService->unlikePost($id),
            SuccessMessages::LIKE_TOGGLE_SUCCESS,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
} 