<?php

namespace App\Observers;

use App\Models\Comment;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;

class CommentObserver
{
    /**
     * Xử lý sự kiện "deleting" của Comment
     * @param Comment $comment Bình luận
     * @return void
     */
    public function deleting(Comment $comment): void
    {
        try {
            // Xóa tất cả thông báo liên quan đến bình luận này
            $deletedCount = DatabaseNotification::where('data->comment_id', $comment->id)->delete();
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa thông báo cho bình luận ID: {$comment->id} - " . $e->getMessage());
        }
    }

    /**
     * Xử lý sự kiện "deleted" của Comment
     * @param Comment $comment Bình luận
     * @return void
     */
    public function deleted(Comment $comment): void
    {
        Log::info("Bình luận ID: {$comment->id} đã được xóa hoàn toàn");
    }
} 