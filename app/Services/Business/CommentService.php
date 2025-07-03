<?php

namespace App\Services\Business;

use App\Exceptions\BusinessException;
use App\Interfaces\CommentRepositoryInterface;
use App\Services\BaseService;
use App\Services\Business\NotificationService;
use App\Services\Infrastructure\CacheService;
use App\Models\Comment;
use App\Models\User;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Reply\StoreReplyRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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
        return $this->executeWithExceptionHandling(function() use ($imageId, $request) {
            $comments = $this->commentRepository->getComments($imageId, $request->input('comment_id'), $request->input('page'));
            
            // Format comments using CommentResource
            $formattedComments = CommentResource::collection($comments['comments'] ?? $comments);
            
            // Return with proper structure
            if (isset($comments['hasMore'])) {
                return [
                    'comments' => $formattedComments,
                    'hasMore' => $comments['hasMore']
                ];
            }
            
            return $formattedComments;
        }, "Getting comments for image ID: {$imageId}");
    }
    
    public function storeComment(StoreCommentRequest $request)
    {
        return $this->executeInTransactionSafely(function() use ($request) {
            $comment = $this->commentRepository->storeComment($request->validated());
            $comment->is_new = true;
            
            // Load user relationship cho CommentResource
            $comment->load('user', 'replies.user');

            // Clear cache cho image comment count
            $this->clearImageCommentCache($comment->image_id);

            // Gửi thông báo bình luận mới
            $this->sendCommentNotification($comment);

            // Log action
            $this->logAction('Comment created', [
                'comment_id' => $comment->id,
                'image_id' => $comment->image_id,
                'user_id' => Auth::id()
            ]);

            return new CommentResource($comment);
        }, "Creating comment for image ID: {$request->input('image_id')}");
    }
    
    public function storeReply(StoreReplyRequest $request, Comment $comment)
    {
        return $this->executeInTransactionSafely(function() use ($request, $comment) {
            $reply = $this->commentRepository->storeReply($request->validated(), $comment);
            $reply->is_new = true;
            
            // Load user relationship cho CommentResource
            $reply->load('user');

            // Clear cache cho image comment count  
            $this->clearImageCommentCache($reply->image_id);

            // Gửi thông báo phản hồi
            $this->sendReplyNotification($reply, $comment);
            
            // Đưa bình luận gốc lên đầu nếu có
            $this->moveOriginCommentToTop($reply);

            // Log action
            $this->logAction('Reply created', [
                'reply_id' => $reply->id,
                'parent_comment_id' => $comment->id,
                'image_id' => $reply->image_id,
                'user_id' => Auth::id()
            ]);

            return new CommentResource($reply);
        }, "Creating reply for comment ID: {$comment->id}");
    }
    
    public function destroy(Comment $comment): bool
    {
        return $this->executeInTransactionSafely(function() use ($comment) {
            if (Gate::denies('delete', $comment)) {
                throw BusinessException::invalidPermissions('delete comment');
            }
            
            $this->commentRepository->deleteComment($comment);
            return true;
        }, "Deleting comment ID: {$comment->id}");
    }
    
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        return $this->executeInTransactionSafely(function() use ($request, $comment) {
            if (Gate::denies('update', $comment)) {
                throw BusinessException::invalidPermissions('update comment');
            }
            
            return $this->commentRepository->updateComment($comment, $request->input('content'));
        }, "Updating comment ID: {$comment->id}");
    }
    
    public function toggleLike(Comment $comment): array
    {
        return $this->executeInTransactionSafely(function() use ($comment) {
            $userId = Auth::id();
            if (!$userId) {
                throw BusinessException::invalidPermissions('like comment (not authenticated)');
            }
            
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
                $this->sendLikeNotification($comment, $userId);
                $this->moveOriginCommentToTop($comment);
            }

            return [
                'likes' => $comment->sum_like,
                'isLiked' => in_array($userId, $listLike)
            ];
        }, "Toggling like for comment ID: {$comment->id}");
    }
    
    /**
     * Đưa bình luận gốc lên đầu danh sách
     */
    private function moveOriginCommentToTop(Comment $comment): void
    {
        if (!$comment->origin_comment) {
            return;
        }

        $this->executeWithExceptionHandling(function() use ($comment) {
            if ($this->commentRepository->touchComment($comment->origin_comment)) {
                $this->logAction("Moved origin comment to top", [
                    'origin_comment_id' => $comment->origin_comment
                ]);
            }
        }, "Moving origin comment to top for comment ID: {$comment->id}");
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
     * Helper methods với exception handling
     */
    private function clearImageCommentCache(int $imageId): void
    {
        $this->executeWithExceptionHandling(function() use ($imageId) {
            $this->cacheService->clearImageCommentCache($imageId);
        }, "Clearing comment cache for image ID: $imageId");
    }
    
    private function sendCommentNotification(Comment $comment): void
    {
        $this->executeWithExceptionHandling(function() use ($comment) {
            $this->notificationService->sendCommentNotification($comment, Auth::id());
        }, "Sending comment notification for comment ID: {$comment->id}");
    }
    
    private function sendReplyNotification(Comment $reply, Comment $comment): void
    {
        $this->executeWithExceptionHandling(function() use ($reply, $comment) {
            $this->notificationService->sendReplyNotification($reply, $comment, Auth::id());
        }, "Sending reply notification for reply ID: {$reply->id}");
    }
    
    private function sendLikeNotification(Comment $comment, int $userId): void
    {
        $this->executeWithExceptionHandling(function() use ($comment, $userId) {
            $this->notificationService->sendLikeNotification($comment, $userId);
        }, "Sending like notification for comment ID: {$comment->id}");
    }

    /**
     * @deprecated Method được giữ lại để backward compatibility
     */
    public function sendNotification(Comment $comment, User $liker): void
    {
        $this->sendLikeNotification($comment, $liker->id);
    }
}