<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;


class CommentController extends Controller
{
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
        
        if ($commentId) {
            // Lấy phản hồi cho một bình luận cụ thể
            $replies = Comment::with(['user'])
                ->where('parent_id', $commentId)
                ->latest()
                ->paginate(self::REPLIES_PER_PAGE);
                
            return response()->json([
                'replies' => collect($replies->items())->map(function ($reply) {
                    return $this->formatComment($reply);
                })->toArray(),
                'hasMore' => $replies->hasMorePages()
            ]);
        }
        
        // Lấy danh sách bình luận chính
        $comments = Comment::with(['user', 'replies' => function ($query) {
            $query->with('user')
                ->latest()
                ->limit(self::REPLIES_PER_PAGE);
        }])
        ->where('image_id', $imageId)
        ->whereNull('parent_id')
        ->latest()
        ->paginate(self::COMMENTS_PER_PAGE);
        
        return response()->json([
            'comments' => collect($comments->items())->map(function ($comment) {
                return $this->formatComment($comment);
            })->toArray(),
            'hasMore' => $comments->hasMorePages()
        ]);
    }

    /**
     * Tạo bình luận mới
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'image_id' => 'required|exists:images,id',
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id'
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'image_id' => $request->image_id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'sum_like' => 0,
            'list_like' => json_encode([])
        ]);

        $comment->load(['user', 'replies.user']);

        return response()->json($this->formatComment($comment), 201);
    }

    /**
     * Xóa một bình luận
     */
    public function destroy(Comment $comment): JsonResponse
    {
        if (Auth::id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Xóa cả replies nếu là comment cha
        if ($comment->parent_id === null) {
            $comment->replies()->delete();
        }
        
        $comment->delete();
        return response()->json(['message' => 'Bình luận đã được xóa thành công'], 200);
    }

    /**
     * Cập nhật một bình luận
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        if (Auth::id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $comment->update([
            'content' => $request->content
        ]);

        return response()->json($this->formatComment($comment), 200);
    }

    /**
     * Thích hoặc bỏ thích một bình luận
     */
    public function toggleLike(Comment $comment): JsonResponse
    {
        $userId = Auth::id();
        $listLike = json_decode($comment->list_like ?? '[]', true);
        
        if (in_array($userId, $listLike)) {
            // Bỏ thích
            $listLike = array_diff($listLike, [$userId]);
            $comment->sum_like = max(0, $comment->sum_like - 1);
        } else {
            // Thích
            $listLike[] = $userId;
            $comment->sum_like += 1;
        }
        
        $comment->list_like = json_encode($listLike);
        $comment->save();
        
        return response()->json([
            'likes' => $comment->sum_like,
            'isLiked' => in_array($userId, $listLike)
        ]);
    }

    /**
     * Format comment để trả về cho frontend
     */
    private function formatComment(Comment $comment): array
    {
        $userId = Auth::id();
        $listLike = json_decode($comment->list_like ?? '[]', true);
        
        $formattedComment = [
            'id' => $comment->id,
            'username' => $comment->user->name,
            'avatar' => $comment->user->avatar_url,
            'text' => $comment->content,
            'time' => $comment->created_at->diffForHumans(),
            'likes' => $comment->sum_like,
            'isLiked' => in_array($userId, $listLike),
            'isOwner' => $userId === $comment->user_id,
            'showAllReplies' => true,
            'replies' => []
        ];
        
        if ($comment->replies->count() > 0) {
            foreach ($comment->replies as $reply) {
                $replyListLike = json_decode($reply->list_like ?? '[]', true);
                
                $formattedComment['replies'][] = [
                    'id' => $reply->id,
                    'username' => $reply->user->name,
                    'avatar' => $reply->user->avatar_url,
                    'text' => $reply->content,
                    'time' => $reply->created_at->diffForHumans(),
                    'likes' => $reply->sum_like,
                    'isLiked' => in_array($userId, $replyListLike),
                    'isOwner' => $userId === $reply->user_id
                ];
            }
        }
        
        return $formattedComment;
    }
}
