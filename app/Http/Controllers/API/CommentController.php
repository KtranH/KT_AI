<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ReplyResource;
use App\Services\CommentService;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected CommentService $commentService;
    public function __construct(CommentService $commentService) 
    {
        $this->commentService = $commentService;
    }
    public function getComments(int $imageId, Request $request)
    {
        try{
            $result = $this->commentService->getComments($imageId, $request);
            if (isset($result['replies'])) {
                return response()->json([
                    'replies' => ReplyResource::collection($result['replies']),
                    'hasMore' => $result['hasMore'],
                ]);
            }
            
            return response()->json([
                'comments' => CommentResource::collection($result['comments']),
                'hasMore' => $result['hasMore']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi lấy bình luận'], 500);
        }
    }
    public function store(StoreCommentRequest $request)
    {
        try{
            $comment = $this->commentService->storeComment($request);
            return response()->json(new CommentResource($comment), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi tạo bình luận'], 500);
        }
    }
    public function storeReply(StoreReplyRequest $request, Comment $comment)
    {
        try{
            $reply = $this->commentService->storeReply($request, $comment);
            return response()->json(new ReplyResource($reply), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi tạo phản hồi'], 500);
        }
    }
    public function destroy(Comment $comment)
    {
        try {
            $result = $this->commentService->deleteComment($comment);
            if($result)
            {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa thành công'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa'
            ], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa bình luận'], 500);
        }
    }
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        try {
            $comment = $this->commentService->update($request, $comment);
            return response()->json(new CommentResource($comment));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật bình luận'], 500);
        }
    }
    public function toggleLike(Comment $comment)
    {
        try {
            $result = $this->commentService->toggleLike($comment);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi thay đổi trạng thái thích'], 500);
        }
    }
}
