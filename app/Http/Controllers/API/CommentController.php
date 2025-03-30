<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function __construct( private readonly CommentService $commentService) {}

    //
    private const COMMENTS_PER_PAGE = 5;
    private const REPLIES_PER_PAGE = 3;

    /**
     * Lấy danh sách bình luận cho một hình ảnh
     */
    public function getComments(int $imageId, Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $commentId = $request->input('comment_id');
        
        $result = $this->commentService->getComments($imageId, $commentId, $page);
        
        if (isset($result['replies'])) {
            return response()->json([
                'replies' => CommentResource::collection($result['replies']),
                'hasMore' => $result['hasMore']
            ]);
        }
        
        return response()->json([
            'comments' => CommentResource::collection($result['comments']),
            'hasMore' => $result['hasMore']
        ]);
    }

    /**
     * Tạo bình luận mới
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $comment = $this->commentService->storeComment($request->validated());
        return response()->json(new CommentResource($comment), 201);
    }

    /**
     * Xóa một bình luận
     */
    public function destroy(Comment $comment): JsonResponse
    {
        if (Gate::denies('delete', $comment)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $this->commentService->deleteComment($comment);
        return response()->json(['message' => 'Bình luận đã được xóa thành công']);
    }

    /**
     * Cập nhật một bình luận
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if (Gate::denies('update', $comment)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $comment = $this->commentService->updateComment($comment, $request->input('content'));
        return response()->json(new CommentResource($comment));
    }

    /**
     * Thích hoặc bỏ thích một bình luận
     */
    public function toggleLike(Comment $comment): JsonResponse
    {
        $result = $this->commentService->toggleLike($comment);
        return response()->json($result);
    }
}
