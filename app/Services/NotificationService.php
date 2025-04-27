<?php

namespace App\Services;
use App\Interfaces\NotificationRepositoryInterface;
use App\Http\Resources\NotificationCollection;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    protected $notificationRepository;
    protected $userRepository;
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
}
