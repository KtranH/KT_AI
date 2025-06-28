<?php

namespace App\Services;
use App\Interfaces\CommentRepositoryInterface;
use App\Services\NotificationService;
use App\Services\CacheService;
use App\Models\Comment;
use App\Models\User;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class CommentService extends BaseService
{
    protected CommentRepositoryInterface $commentRepository;
    protected NotificationService $notificationService;
    protected CacheService $cacheService;
    
    public function __construct(
        CommentRepositoryInterface $commentRepository,
        NotificationService $notificationService,
        CacheService $cacheService
    ) {
        $this->commentRepository = $commentRepository;
        $this->notificationService = $notificationService;
        $this->cacheService = $cacheService;
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

        // Clear cache cho image comment count
        $this->cacheService->clearImageCommentCache($comment->image_id);

        // Gửi thông báo bình luận mới
        $this->notificationService->sendCommentNotification($comment, Auth::id());

        // Log action
        $this->logAction('Comment created', [
            'comment_id' => $comment->id,
            'image_id' => $comment->image_id,
            'user_id' => Auth::id()
        ]);

        return $comment;
    }
    
    public function storeReply(StoreReplyRequest $request, Comment $comment)
    {
        $reply = $this->commentRepository->storeReply($request->validated(), $comment);
        $reply->is_new = true;

        // Clear cache cho image comment count  
        $this->cacheService->clearImageCommentCache($reply->image_id);

        // Gửi thông báo phản hồi
        $this->notificationService->sendReplyNotification($reply, $comment, Auth::id());
        
        // Đưa bình luận gốc lên đầu nếu có
        $this->moveOriginCommentToTop($reply);

        // Log action
        $this->logAction('Reply created', [
            'reply_id' => $reply->id,
            'parent_comment_id' => $comment->id,
            'image_id' => $reply->image_id,
            'user_id' => Auth::id()
        ]);

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
            if ($this->commentRepository->touchComment($comment->origin_comment)) {
                Log::info("Đã đưa bình luận gốc ID:{$comment->origin_comment} lên đầu danh sách");
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
        // Kiểm tra cache trước
        $cachedCount = $this->cacheService->getImageCommentCount($imageId);
        if ($cachedCount !== null) {
            return $cachedCount;
        }
        
        // Nếu không có cache, query từ DB và cache lại
        $count = $this->commentRepository->countByImageId($imageId);
        $this->cacheService->cacheImageCommentCount($imageId, $count);
        
        return $count;
    }
    
    /**
     * Bulk like/unlike comments
     */
    public function bulkToggleLike(array $commentIds, int $userId): array
    {
        $results = [];
        
        foreach ($commentIds as $commentId) {
            $comment = $this->commentRepository->findById($commentId);
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