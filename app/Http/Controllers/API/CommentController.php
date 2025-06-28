<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Http\Controllers\SuccessMessages;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ReplyResource;
use App\Services\CommentService;
use App\Models\Comment;
use Illuminate\Auth\Access\AuthorizationException;
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
            function() use ($imageId, $request) {
                $result = $this->commentService->getComments($imageId, $request);
                
                if (isset($result['replies'])) {
                    return [
                        'replies' => ReplyResource::collection($result['replies']),
                        'hasMore' => $result['hasMore'],
                    ];
                }
                
                return [
                    'comments' => CommentResource::collection($result['comments']),
                    'hasMore' => $result['hasMore']
                ];
            },
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
            function() use ($request) {
                $comment = $this->commentService->storeComment($request);
                return new CommentResource($comment);
            },
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
            function() use ($request, $comment) {
                $reply = $this->commentService->storeReply($request, $comment);
                return new ReplyResource($reply);
            },
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
            function() use ($comment) {
                $result = $this->commentService->destroy($comment);
                if (!$result) {
                    return response()->json([
                        'message' => 'Không được phép xóa bình luận này'
                    ], 403);
                }
                return ['message' => 'Xóa bình luận thành công'];
            },
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
        try {
            return $this->executeServiceMethod(
                function() use ($request, $comment) {
                    $updatedComment = $this->commentService->update($request, $comment);
                    return new CommentResource($updatedComment);
                },
                SuccessMessages::SUCCESS_UPDATE,
                ErrorMessages::COMMENT_UPDATE_ERROR
            );
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        }
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
