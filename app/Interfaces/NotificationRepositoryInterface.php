<?php

namespace App\Interfaces;

interface NotificationRepositoryInterface
{
    // Lấy thông báo
    public function getNotification($user);

    // Đếm thông báo chưa đọc
    public function countUnreadNotifications($user);

    // Tìm kiếm thông báo
    public function findNotification($id);

    // Cập nhật thông báo đã đọc
    public function updateReadAllNotifications($user);
}

