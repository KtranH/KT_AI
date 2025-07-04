<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Social;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Http\Requests\V1\Social\StoreCommentRequest;
use App\Http\Requests\V1\Social\UpdateCommentRequest;
use App\Http\Requests\V1\Social\StoreReplyRequest;
use App\Services\Business\CommentService;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends BaseV1Controller
{
    protected CommentService $commentService;
    
    public function __construct(CommentService $commentService) 
    {
        $this->commentService = $commentService;
    }
    
    /**
     * Lấy comments cho một image
     * 
     * @param int $imageId
     * @param Request $request
     * @return JsonResponse
     */
    public function getComments(int $imageId, Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->commentService->getComments($imageId, $request),
            null,
            ErrorMessages::COMMENT_LOAD_ERROR
        );
    }
    
    /**
     * Tạo comment mới
     * 
     * @param StoreCommentRequest $request
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->commentService->storeComment($request),
            SuccessMessages::COMMENT_CREATE_SUCCESS,
            ErrorMessages::COMMENT_CREATE_ERROR
        );
    }
    
    /**
     * Tạo reply cho một comment
     * 
     * @param StoreReplyRequest $request
     * @param Comment $comment
     * @return JsonResponse
     */
    public function storeReply(StoreReplyRequest $request, Comment $comment): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->commentService->storeReply($request, $comment),
            SuccessMessages::REPLY_CREATE_SUCCESS,
            ErrorMessages::REPLY_CREATE_ERROR
        );
    }
    
    /**
     * Cập nhật comment
     * 
     * @param UpdateCommentRequest $request
     * @param Comment $comment
     * @return JsonResponse
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->commentService->update($request, $comment),
            SuccessMessages::COMMENT_UPDATE_SUCCESS,
            ErrorMessages::COMMENT_UPDATE_ERROR
        );
    }
    
    /**
     * Xóa comment
     * 
     * @param Comment $comment
     * @return JsonResponse
     */
    public function destroy(Comment $comment): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->commentService->destroy($comment),
            SuccessMessages::COMMENT_DELETE_SUCCESS,
            ErrorMessages::COMMENT_DELETE_ERROR
        );
    }
    
    /**
     * Toggle like cho comment
     * 
     * @param Comment $comment
     * @return JsonResponse
     */
    public function toggleLike(Comment $comment): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->commentService->toggleLike($comment),
            SuccessMessages::LIKE_TOGGLE_SUCCESS,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
} 