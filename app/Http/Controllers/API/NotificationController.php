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
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $notifications = DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
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
                'last_page' => $notifications->lastPage()
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