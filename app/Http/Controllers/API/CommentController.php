<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ReplyResource;
use App\Interfaces\CommentRepositoryInterface;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct( private readonly CommentRepositoryInterface $commentRepository) {}

    /**
     * Lấy danh sách bình luận cho một hình ảnh
     */
    public function getComments(int $imageId, Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $commentId = $request->input('comment_id');
        
        $result = $this->commentRepository->getComments($imageId, $commentId, $page);
        
        if (isset($result['replies'])) {
            return response()->json([
                'replies' => ReplyResource::collection($result['replies']),
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
        $comment = $this->commentRepository->storeComment($request->validated());
        return response()->json(new CommentResource($comment), 201);
    }

    /**
     * Tạo phản hồi cho một bình luận
     */
    public function storeReply(StoreReplyRequest $request, Comment $comment): JsonResponse
    {
        // Tìm parent_id đúng (có thể là comment chính hoặc một phản hồi)
        $parentId = $comment->id;
        
        // Tạo phản hồi mới
        $reply = $this->commentRepository->storeReply($request->validated(), $comment);
        
        return response()->json(new ReplyResource($reply), 201);
    }

    /**
     * Xóa một bình luận
     */
    public function destroy(Comment $comment): JsonResponse
    {
        if (Gate::denies('delete', $comment)) {
            return response()->json(['message' => 'Không được phép xóa bình luận này'], 403);
        }
        
        $this->commentRepository->deleteComment($comment);
        return response()->json(['message' => 'Bình luận đã được xóa thành công']);
    }

    /**
     * Cập nhật một bình luận
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        if (Gate::denies('update', $comment)) {
            return response()->json(['message' => 'Không được phép cập nhật bình luận này'], 403);
        }
        
        $comment = $this->commentRepository->updateComment($comment, $request->input('content'));
        return response()->json(new CommentResource($comment));
    }

    /**
     * Thích hoặc bỏ thích một bình luận
     */
    public function toggleLike(Comment $comment): JsonResponse
    {
        $result = $this->commentRepository->toggleLike($comment);
        return response()->json($result);
    }
}
