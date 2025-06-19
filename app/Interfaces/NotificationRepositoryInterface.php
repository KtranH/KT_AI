<?php

namespace App\Interfaces;
use Illuminate\Notifications\DatabaseNotification;

interface NotificationRepositoryInterface
{
    // Lấy thông báo
    public function getNotification($user);

    // Đếm thông báo chưa đọc
    public function countUnreadNotifications($user): int;

    // Tìm kiếm thông báo
    public function findNotification($id): DatabaseNotification;
    
    // Cập nhật thông báo đã đọc 
    public function updateReadAllNotifications($user): void;
}

