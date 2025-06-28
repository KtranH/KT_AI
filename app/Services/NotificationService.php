<?php

namespace App\Services;
use App\Interfaces\NotificationRepositoryInterface;
use App\Http\Resources\NotificationCollection;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\AddCommentNotification;
use App\Notifications\AddReplyNotification;
use App\Notifications\LikeCommentNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $notificationRepository;
    protected $userRepository;
    private const DEFAULT_AVATAR_URL = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
    
    public function __construct(NotificationRepositoryInterface $notificationRepository, UserRepositoryInterface $userRepository) {
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
    }
    public function index(Request $request)
    {
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
    }
    public function markAsRead(string $id)
    {
        $notification = $this->notificationRepository->findNotification($id);
    
        // Kiểm tra xem thông báo có thuộc về người dùng hiện tại không
        if ($notification->notifiable_id != Auth::id() || $notification->notifiable_type != get_class(Auth::user())) {
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
    }
    public function markAllAsRead()
    {
        $user = Auth::user();
        $this->notificationRepository->updateReadAllNotifications($user);

        return response()->json([
            'success' => true,
            'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc'
        ]);
    }
    public function destroy(string $id)
    {
        $notification = $this->notificationRepository->findNotification($id);

        // Kiểm tra xem thông báo có thuộc về người dùng hiện tại không
        if ($notification->notifiable_id != Auth::id() || $notification->notifiable_type != get_class(Auth::user())) {
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
    }

    /**
     * Gửi thông báo khi có bình luận mới
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
        $imageOwner = User::find($imageOwnerId);

        if ($commenter && $imageOwner) {
            try {
                $imageOwner->notify(new AddCommentNotification($commenter, $comment));
                Log::info("Notification sent: New comment from {$commenter->name} to {$imageOwner->name}");
            } catch (\Exception $e) {
                Log::error('Error sending comment notification: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Gửi thông báo khi có phản hồi mới
     */
    public function sendReplyNotification(Comment $reply, Comment $parentComment, int $replierId): void
    {
        $commentOwnerId = $parentComment->user_id;

        if ($commentOwnerId === $replierId) {
            return; // Không gửi thông báo cho chính mình
        }

        $replier = $this->getUserWithDefaultAvatar($replierId);
        $commentOwner = User::find($commentOwnerId);

        if ($replier && $commentOwner) {
            try {
                $commentOwner->notify(new AddReplyNotification($replier, $reply));
                Log::info("Notification sent: New reply from {$replier->name} to {$commentOwner->name}");
            } catch (\Exception $e) {
                Log::error('Error sending reply notification: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Gửi thông báo khi có like
     */
    public function sendLikeNotification(Comment $comment, int $likerId): void
    {
        if ($comment->user_id === $likerId) {
            return; // Không gửi thông báo cho chính mình
        }
        
        $liker = $this->getUserWithDefaultAvatar($likerId);
        $commentOwner = User::find($comment->user_id);

        if ($liker && $commentOwner) {
            try {
                $commentOwner->notify(new LikeCommentNotification($liker, $comment));
                Log::info("Notification sent: Like from {$liker->name} to {$commentOwner->name}");
            } catch (\Exception $e) {
                Log::error('Error sending like notification: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Gửi thông báo hàng loạt
     */
    public function sendBulkNotifications(array $notifications): array
    {
        $results = [];
        
        foreach ($notifications as $notification) {
            try {
                $type = $notification['type'];
                $data = $notification['data'];
                
                match($type) {
                    'comment' => $this->sendCommentNotification($data['comment'], $data['commenter_id']),
                    'reply' => $this->sendReplyNotification($data['reply'], $data['parent_comment'], $data['replier_id']),
                    'like' => $this->sendLikeNotification($data['comment'], $data['liker_id']),
                    default => throw new \InvalidArgumentException("Unknown notification type: {$type}")
                };
                
                $results[] = ['status' => 'success', 'type' => $type];
            } catch (\Exception $e) {
                $results[] = ['status' => 'error', 'type' => $type ?? 'unknown', 'message' => $e->getMessage()];
                Log::error("Bulk notification error: " . $e->getMessage());
            }
        }
        
        return $results;
    }
    
    /**
     * Lấy thông tin user với avatar mặc định nếu cần
     */
    private function getUserWithDefaultAvatar(int $userId): ?User
    {
        $user = User::select('id', 'name', 'avatar_url')->find($userId);
        
        if ($user && $user->avatar_url === null) {
            $user->avatar_url = self::DEFAULT_AVATAR_URL;
        }
        
        return $user;
    }
}
