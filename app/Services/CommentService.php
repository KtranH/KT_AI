<?php

namespace App\Services;
use App\Interfaces\CommentRepositoryInterface;
use App\Services\NotificationService;
use App\Models\Comment;
use App\Models\User;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class CommentService
{
    protected CommentRepositoryInterface $commentRepository;
    protected NotificationService $notificationService;
    
    public function __construct(
        CommentRepositoryInterface $commentRepository,
        NotificationService $notificationService
    ) {
        $this->commentRepository = $commentRepository;
        $this->notificationService = $notificationService;
    }
    
    public function getComments(int $imageId, Request $request)
    {
        $page = $request->input('page', 1);
        $commentId = $request->input('comment_id');
        return $this->commentRepository->getComments($imageId, $commentId, $page);
    }
    
    public function storeComment(StoreCommentRequest $request)
    {
        $comment = $this->commentRepository->storeComment($request->validated());
        $comment->is_new = true;

        // Gửi thông báo bình luận mới
        $this->notificationService->sendCommentNotification($comment, Auth::id());

        return $comment;
    }
    
    public function storeReply(StoreReplyRequest $request, Comment $comment)
    {
        $reply = $this->commentRepository->storeReply($request->validated(), $comment);
        $reply->is_new = true;

        // Gửi thông báo phản hồi
        $this->notificationService->sendReplyNotification($reply, $comment, Auth::id());
        
        // Đưa bình luận gốc lên đầu nếu có
        $this->moveOriginCommentToTop($reply);

        return $reply;
    }
    
    public function destroy(Comment $comment): bool
    {
        if (Gate::denies('delete', $comment)) {
            return false;
        }
        
        $this->commentRepository->deleteComment($comment);
        return true;
    }
    
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        if (Gate::denies('update', $comment)) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Không được phép cập nhật bình luận này');
        }
        
        return $this->commentRepository->updateComment($comment, $request->input('content'));
    }
    
    public function toggleLike(Comment $comment): array
    {
        $userId = Auth::id();
        $listLike = $comment->list_like ?? [];
        $wasLiked = in_array($userId, $listLike);

        if ($wasLiked) {
            $listLike = array_diff($listLike, [$userId]);
            $comment->sum_like = max(0, $comment->sum_like - 1);
        } else {
            $listLike[] = $userId;
            $comment->sum_like += 1;
        }

        $comment->list_like = $listLike;
        $comment->save();

        // Xử lý thông báo và đưa bình luận lên đầu
        if (!$wasLiked && $comment->user_id !== $userId) {
            $this->notificationService->sendLikeNotification($comment, $userId);
            $this->moveOriginCommentToTop($comment);
        }

        return [
            'likes' => $comment->sum_like,
            'isLiked' => in_array($userId, $listLike)
        ];
    }
    
    /**
     * Đưa bình luận gốc lên đầu danh sách
     */
    private function moveOriginCommentToTop(Comment $comment): void
    {
        if (!$comment->origin_comment) {
            return;
        }

        try {
            $originComment = Comment::find($comment->origin_comment);
            if ($originComment) {
                $originComment->touch();
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi đưa bình luận gốc lên đầu: ' . $e->getMessage());
        }
    }
    
    /**
     * Lấy số lượng bình luận của một ảnh (có cache)
     */
    public function getCommentCount(int $imageId): int
    {
        return \Cache::remember("image_comments_count_{$imageId}", 300, function() use ($imageId) {
            return Comment::where('image_id', $imageId)->where('parent_id', null)->count();
        });
    }
    
    /**
     * Bulk like/unlike comments
     */
    public function bulkToggleLike(array $commentIds, int $userId): array
    {
        $results = [];
        
        foreach ($commentIds as $commentId) {
            $comment = Comment::find($commentId);
            if ($comment) {
                $results[$commentId] = $this->toggleLike($comment);
            }
        }
        
        return $results;
    }
    
    /**
     * Kiểm tra quyền trước khi thực hiện action
     */
    public function canPerformAction(Comment $comment, string $action): bool
    {
        return match($action) {
            'update', 'delete' => Gate::allows($action, $comment),
            'like' => Auth::check(),
            default => false
        };
    }
    
    /**
     * @deprecated Method được giữ lại để backward compatibility
     */
    public function sendNotification(Comment $comment, User $liker): void
    {
        $this->notificationService->sendLikeNotification($comment, $liker->id);
    }
}