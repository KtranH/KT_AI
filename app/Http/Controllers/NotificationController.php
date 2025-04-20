<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Lấy danh sách thông báo của người dùng, có hỗ trợ phân trang
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->input('per_page', 10);
        $paginate = filter_var($request->input('paginate', false), FILTER_VALIDATE_BOOLEAN);
        
        $query = DatabaseNotification::where('notifiable_id', $user->id)
                                   ->where('notifiable_type', get_class($user))
                                   ->orderBy('created_at', 'desc');
        
        if ($paginate) {
            // Trả về danh sách thông báo có phân trang
            $notifications = $query->paginate($perPage);
            
            return response()->json($notifications);
        } else {
            // Trả về danh sách thông báo không phân trang, giới hạn số lượng
            $notifications = $query->limit($perPage)->get();
            
            return response()->json($notifications);
        }
    }

    /**
     * Lấy số lượng thông báo chưa đọc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = DatabaseNotification::where('notifiable_id', $user->id)
                                  ->where('notifiable_type', get_class($user))
                                  ->whereNull('read_at')
                                  ->count();
        
        return response()->json(['count' => $count]);
    }

    /**
     * Đánh dấu thông báo đã đọc
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id): JsonResponse
    {
        $user = Auth::user();
        $notification = DatabaseNotification::where('id', $id)
                                         ->where('notifiable_id', $user->id)
                                         ->where('notifiable_type', get_class($user))
                                         ->firstOrFail();
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Thông báo đã được đánh dấu là đã đọc'
        ]);
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        DatabaseNotification::where('notifiable_id', $user->id)
                         ->where('notifiable_type', get_class($user))
                         ->whereNull('read_at')
                         ->update(['read_at' => now()]);
        
        return response()->json([
            'success' => true,
            'message' => 'Tất cả thông báo đã được đánh dấu là đã đọc'
        ]);
    }
} 