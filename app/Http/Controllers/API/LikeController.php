<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Http\Controllers\SuccessMessages;
use App\Http\Resources\LikeResource;
use App\Services\Business\LikeService;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    protected LikeService $likeService;
    
    public function __construct(LikeService $likeService) 
    {
        $this->likeService = $likeService;
    }
    
    /**
     * Kiểm tra xem một bài viết đã được thích hay chưa
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function checkLiked(int $id): JsonResponse
    {
        // Validate ID
        if (!$this->isValidId($id)) {
            return $this->errorResponse('ID hình ảnh không hợp lệ', 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->likeService->checkLiked($id),
            null,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
    
    /**
     * Lấy danh sách các bài viết đã được thích
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getLikes(int $id): JsonResponse
    {
        // Validate ID
        if (!$this->isValidId($id)) {
            return $this->errorResponse('ID hình ảnh không hợp lệ', 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->likeService->getLikes($id),
            null,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
    
    /**
     * Thích một bài viết
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function likePost(int $id): JsonResponse
    {
        // Validate ID
        if (!$this->isValidId($id)) {
            return $this->errorResponse('ID hình ảnh không hợp lệ', 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->likeService->likePost($id),
            SuccessMessages::LIKE_TOGGLE_SUCCESS,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
    
    /**
     * Bỏ thích một bài viết
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function unlikePost(int $id): JsonResponse
    {
        // Validate ID
        if (!$this->isValidId($id)) {
            return $this->errorResponse('ID hình ảnh không hợp lệ', 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->likeService->unlikePost($id),
            SuccessMessages::LIKE_TOGGLE_SUCCESS,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
}
