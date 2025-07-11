<?php

namespace App\Repositories;

use App\Interfaces\NotificationRepositoryInterface;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Lấy query builder cho thông báo của người dùng
     * @param User $user Người dùng
     * @return Builder Query builder cho thông báo
     */
    public function getNotification(User $user): Builder
    {
        $query = DatabaseNotification::query();
        
        if (!$user) {
            return $query->whereRaw('1=0');
        }

        return $query->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);
    }

    /**
     * Đếm số lượng thông báo chưa đọc
     * @param User $user Người dùng
     * @return int Số lượng thông báo chưa đọc
     */
    public function countUnreadNotifications(User $user): int
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
     * @param int|string $id ID của thông báo (UUID string)
     * @return DatabaseNotification Thông báo
     */
    public function findNotification(int|string $id): DatabaseNotification
    {
        // DatabaseNotification sử dụng UUID làm primary key
        return DatabaseNotification::findOrFail($id);
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     * @param User $user Người dùng
     * @return void
     */
    public function updateReadAllNotifications(User $user): void
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
