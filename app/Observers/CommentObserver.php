<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class CommentObserver
{
    /**
     * Handle the Comment "deleting" event.
     */
    public function deleting(Comment $comment): void
    {
        try {
            // Xóa tất cả thông báo liên quan đến bình luận này
            $deletedCount = DatabaseNotification::where('data->comment_id', $comment->id)->delete();
            
            Log::info("Đã xóa {$deletedCount} thông báo liên quan đến bình luận ID: {$comment->id}");
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa thông báo cho bình luận ID: {$comment->id} - " . $e->getMessage());
        }
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        Log::info("Bình luận ID: {$comment->id} đã được xóa hoàn toàn");
    }
} 