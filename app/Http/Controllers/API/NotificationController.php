<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationCollection;
use App\Interfaces\NotificationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationRepositoryInterface $notificationRepository) {}
    /**
     * Lấy danh sách thông báo của người dùng hiện tại
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): ResourceCollection
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

    /**
     * Đánh dấu thông báo đã đọc
     *
     * @param string $id
     * @return JsonResponse
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = DatabaseNotification::findOrFail($id);

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

    /**
     * Đánh dấu tất cả thông báo đã đọc
     *
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $this->notificationRepository->updateReadAllNotifications($user);

        return response()->json([
            'success' => true,
            'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc'
        ]);
    }

    /**
     * Xóa thông báo
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
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