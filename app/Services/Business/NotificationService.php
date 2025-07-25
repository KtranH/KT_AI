<?php

namespace App\Services\Business;

use App\Interfaces\NotificationRepositoryInterface;
use App\Http\Resources\V1\Social\NotificationCollection;
use App\Services\BaseService;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\AddCommentNotification;
use App\Notifications\AddReplyNotification;
use App\Notifications\LikeCommentNotification;
use Illuminate\Http\Request;        
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class NotificationService extends BaseService
{
    protected $notificationRepository;
    protected $userRepository;
    private const DEFAULT_AVATAR_URL = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
    
    public function __construct(
        NotificationRepositoryInterface $notificationRepository, 
        UserRepositoryInterface $userRepository
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Lấy danh sách thông báo
     * @param Request $request Request object
     * @return NotificationCollection Kết quả
     */
    public function index(Request $request): NotificationCollection
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            $user = Auth::user();
            $filter = $request->input('filter', 'all');
            $perPage = $request->input('per_page', 10);
            $query = $this->notificationRepository->getNotification($user);

            // Áp dụng bộ lọc nếu có
            if ($filter === 'unread') {
                $query->whereNull('read_at');
            } elseif ($filter === 'read') {
                $query->whereNotNull('read_at');
            }

            // Sắp xếp theo thời gian tạo giảm dần (mới nhất lên đầu)
            $query->orderBy('created_at', 'desc');

            // Phân trang kết quả
            $notifications = $query->paginate($perPage);

            // Đếm số lượng thông báo chưa đọc
            $unreadCount = $this->notificationRepository->countUnreadNotifications($user);

            return new NotificationCollection($notifications, $unreadCount);
        }, "Getting notifications for user ID: " . Auth::id());
    }

    /**
     * Đánh dấu thông báo đã đọc
     * @param string $id ID của thông báo
     * @return JsonResponse Kết quả
     */
    public function markAsRead(string $id): JsonResponse
    {
        return $this->executeWithExceptionHandling(function() use ($id) {
            $notification = $this->notificationRepository->findNotification($id);
        
            // Kiểm tra xem thông báo có thuộc về người dùng hiện tại không
            if ($notification->notifiable_id ?? 'unknown' != Auth::id() || $notification->notifiable_type ?? 'unknown' != get_class(Auth::user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập thông báo này'
                ], 403);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Thông báo đã được đánh dấu là đã đọc'
            ]);
            }, "Marking notification as read for ID: {$id}");
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     * @return JsonResponse Kết quả
     */
    public function markAllAsRead()
    {
        return $this->executeWithExceptionHandling(function() {
                $user = Auth::user();
                $this->notificationRepository->updateReadAllNotifications($user);

            return response()->json([
                'success' => true,
                'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc'
            ]);
        }, "Marking all notifications as read");
    }

    /**
     * Xóa thông báo
     * @param string $id ID của thông báo
     * @return JsonResponse Kết quả
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->executeInTransactionSafely(function() use ($id) {
            $notification = $this->notificationRepository->findNotification($id);

            // Kiểm tra xem thông báo có thuộc về người dùng hiện tại không
            if ($notification->notifiable_id ?? 'unknown' != Auth::id() || $notification->notifiable_type ?? 'unknown' != get_class(Auth::user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập thông báo này'
                ], 403);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Thông báo đã được xóa'
            ]);
        }, "Deleting notification for ID: {$id}");
    }

    /**
     * Gửi thông báo khi có bình luận mới
     * @param Comment $comment Bình luận
     * @param int $commenterId ID của người bình luận
     * @return void
     */
    public function sendCommentNotification(Comment $comment, int $commenterId): void
    {
        if (!$comment->image) {
            return;
        }

        $imageOwnerId = $comment->image->user_id;

        if ($imageOwnerId === $commenterId) {
            return; // Không gửi thông báo cho chính mình
        }

        $commenter = $this->getUserWithDefaultAvatar($commenterId);
        $imageOwner = $this->userRepository->findById($imageOwnerId);

        if ($commenter && $imageOwner) {
            $this->executeWithExceptionHandling(function() use ($commenter, $imageOwner, $comment) {
                $imageOwner->notify(new AddCommentNotification($commenter, $comment));
                Log::info("Notification sent: New comment from {$commenter->name} to {$imageOwner->name}");
            }, "Sending comment notification for comment ID: {$comment->id}");
        }
    }
    
    /**
     * Gửi thông báo khi có phản hồi mới
     * @param Comment $reply Phản hồi
     * @param Comment $parentComment Bình luận cha
     * @param int $replierId ID của người phản hồi
     * @return void
     */
    public function sendReplyNotification(Comment $reply, Comment $parentComment, int $replierId): void
    {
        $commentOwnerId = $parentComment->user_id;

        if ($commentOwnerId === $replierId) {
            return; // Không gửi thông báo cho chính mình
        }

        $replier = $this->getUserWithDefaultAvatar($replierId);
        $commentOwner = $this->userRepository->findById($commentOwnerId);

        if ($replier && $commentOwner) {
            $this->executeWithExceptionHandling(function() use ($replier, $commentOwner, $reply) {
                $commentOwner->notify(new AddReplyNotification($replier, $reply));
                Log::info("Notification sent: New reply from {$replier->name} to {$commentOwner->name}");
            }, "Sending reply notification for reply ID: {$reply->id}");
        }
    }
    
    /**
     * Gửi thông báo khi có like
     * @param Comment $comment Bình luận
     * @param int $likerId ID của người like
     * @return void
     */
    public function sendLikeNotification(Comment $comment, int $likerId): void
    {
        if ($comment->user_id === $likerId) {
            return; // Không gửi thông báo cho chính mình
        }
        
        $liker = $this->getUserWithDefaultAvatar($likerId);
        $commentOwner = $this->userRepository->findById($comment->user_id);

        if ($liker && $commentOwner) {
            $this->executeWithExceptionHandling(function() use ($liker, $commentOwner, $comment) {
                $commentOwner->notify(new LikeCommentNotification($liker, $comment));
                Log::info("Notification sent: Like from {$liker->name} to {$commentOwner->name}");
            }, "Sending like notification for comment ID: {$comment->id}");
        }
    }
    
    /**
     * Gửi thông báo hàng loạt
     * @param array $notifications Danh sách thông báo
     * @return array Kết quả
     */
    public function sendBulkNotifications(array $notifications): array
    {
        return $this->executeWithExceptionHandling(function() use ($notifications) {
            $results = [];
            
            foreach ($notifications as $notification) {
                $type = $notification['type'];
                $data = $notification['data'];
                
                match($type) {
                    'comment' => $this->sendCommentNotification($data['comment'], $data['commenter_id']),
                    'reply' => $this->sendReplyNotification($data['reply'], $data['parent_comment'], $data['replier_id']),
                    'like' => $this->sendLikeNotification($data['comment'], $data['liker_id']),
                    default => throw new \InvalidArgumentException("Unknown notification type: {$type}")
                };
                
                $results[] = ['status' => 'success', 'type' => $type];
            }
            return $results;
        }, "Sending bulk notifications");
    }

    /**
     * Xóa thông báo like comment khi bỏ like
     * @param Comment $comment Bình luận
     * @param int $unlikerId ID của người bỏ like
     * @return void
     */
    public function deleteLikeNotification(Comment $comment, int $unlikerId): void
    {
        $this->executeWithExceptionHandling(function() use ($comment, $unlikerId) {
            // Tìm thông báo like comment của user này
            $commentOwner = $this->userRepository->findById($comment->user_id);
            if (!$commentOwner) {
                return;
            }

            // Xóa notification có type = 'like_comment', liker_id = $unlikerId, comment_id = $comment->id
            $deletedCount = $commentOwner->notifications()
                ->where('type', 'App\\Notifications\\LikeCommentNotification')
                ->whereJsonContains('data->liker_id', $unlikerId)
                ->whereJsonContains('data->comment_id', $comment->id)
                ->delete();

        }, "Deleting like notification for comment ID: {$comment->id} by user ID: {$unlikerId}");
    }
    
    /**
     * Lấy thông tin user với avatar mặc định nếu cần
     * @param int $userId ID của người dùng
     * @return ?User Kết quả
     */
    private function getUserWithDefaultAvatar(int $userId): ?User
    {
        return $this->executeWithExceptionHandling(function() use ($userId) {
            $user = $this->userRepository->getUserForNotification($userId);
        
            if ($user && $user->avatar_url === null) {
                $user->avatar_url = self::DEFAULT_AVATAR_URL;
            }
            
            return $user;
        }, "Getting user with default avatar for user ID: {$userId}");
    }
}
