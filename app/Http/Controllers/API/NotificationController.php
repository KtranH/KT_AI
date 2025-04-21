<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của người dùng hiện tại
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $filter = $request->input('filter', 'all');
        $perPage = $request->input('per_page', 10);
        
        $query = DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);
            
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
        $unreadCount = DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
        
        return response()->json([
            'notifications' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'has_more_pages' => $notifications->hasMorePages(),
            ],
            'unread_count' => $unreadCount
        ]);
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
        
        DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        
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
        $notification = DatabaseNotification::findOrFail($id);
        
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