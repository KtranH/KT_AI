<?php

namespace App\Repositories;

use App\Interfaces\NotificationRepositoryInterface;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function getNotification($user)
    {
        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);
    }
    
    public function countUnreadNotifications($user)
    {
        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }
    
    public function findNotification($id)
    {
        return DatabaseNotification::findOrFail($id);
    }
    
    public function updateReadAllNotifications($user)
    {
        DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
