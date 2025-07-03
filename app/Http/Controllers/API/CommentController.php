<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Http\Controllers\SuccessMessages;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Services\Business\CommentService;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    protected CommentService $commentService;
    
    public function __construct(CommentService $commentService) 
    {
        $this->commentService = $commentService;
    }
    
    /**
     * Lấy các comment cho một ảnh
     * 
     * @param int $imageId
     * @param Request $request
     * @return JsonResponse
     */
    public function getComments(int $imageId, Request $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->commentService->getComments($imageId, $request),
            null,
            ErrorMessages::COMMENT_LOAD_ERROR
        );
    }
    
    /**
     * Lưu một comment mới
     * 
     * @param StoreCommentRequest $request
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->commentService->storeComment($request),
            SuccessMessages::SUCCESS_CREATE,
            ErrorMessages::COMMENT_CREATE_ERROR
        );
    }
    
    /**
     * Lưu một trả lời cho một comment
     * 
     * @param StoreReplyRequest $request
     * @param Comment $comment
     * @return JsonResponse
     */
    public function storeReply(StoreReplyRequest $request, Comment $comment): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->commentService->storeReply($request, $comment),
            SuccessMessages::SUCCESS_CREATE,
            ErrorMessages::REPLY_CREATE_ERROR
        );
    }
    
    /**
     * Xóa một comment
     * 
     * @param Comment $comment
     * @return JsonResponse
     */
    public function destroy(Comment $comment): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->commentService->destroy($comment),
            null,
            ErrorMessages::COMMENT_DELETE_ERROR
        );
    }
    
    /**
     * Cập nhật một comment
     * 
     * @param UpdateCommentRequest $request
     * @param Comment $comment
     * @return JsonResponse
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->commentService->update($request, $comment),
            SuccessMessages::SUCCESS_UPDATE,
            ErrorMessages::COMMENT_UPDATE_ERROR
        );
    }
    
    /**
     * Chuyển đổi trạng thái thích của một comment
     * 
     * @param Comment $comment
     * @return JsonResponse
     */
    public function toggleLike(Comment $comment): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->commentService->toggleLike($comment),
            null,
            ErrorMessages::LIKE_TOGGLE_ERROR
        );
    }
}
