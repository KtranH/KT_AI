<?php

namespace App\Interfaces;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Builder;

interface NotificationRepositoryInterface
{
    /**
     * Lấy query builder cho thông báo
     * @param User $user Người dùng
     * @return Builder Query builder cho thông báo
     */
    public function getNotification(User $user): Builder;

    /**
     * Đếm thông báo chưa đọc
     * @param User $user Người dùng
     * @return int Số lượng thông báo chưa đọc
     */
    public function countUnreadNotifications(User $user): int;

    /**
     * Tìm kiếm thông báo
     * @param int $id ID của thông báo
     * @return DatabaseNotification Thông báo
     */
    public function findNotification(int $id): DatabaseNotification;
    
    /**
     * Cập nhật thông báo đã đọc
     * @param User $user Người dùng
     * @return void
     */
    public function updateReadAllNotifications(User $user): void;
}

