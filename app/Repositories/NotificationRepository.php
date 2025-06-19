<?php

namespace App\Repositories;

use App\Interfaces\NotificationRepositoryInterface;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Lấy thông báo của người dùng
     */
    public function getNotification($user)
    {
        if (!$user) {
            return DatabaseNotification::query()->whereRaw('1=0'); // Trả về query rỗng
        }

        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);
    }

    /**
     * Đếm số lượng thông báo chưa đọc
     *
     * @param User $user Đối tượng người dùng
     * @return int
     */
    public function countUnreadNotifications($user): int
    {
        if (!$user) {
            return 0;
        }

        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Tìm thông báo theo ID
     *
     * @param string $id ID của thông báo
     * @return DatabaseNotification
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findNotification($id): DatabaseNotification
    {
        return DatabaseNotification::findOrFail($id);
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     *
     * @param User $user Đối tượng người dùng
     * @return void
     */
    public function updateReadAllNotifications($user): void
    {
        if (!$user) {
            return;
        }

        DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
