<?php

namespace App\Repositories;

use App\Interfaces\NotificationRepositoryInterface;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Collection\Collection;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Auth;


class NotificationRepository implements NotificationRepositoryInterface
{
    public function getNotification($user)
    {
        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);
    }
    
    public function countUnreadNotifications($user): int
    {
        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }
    
    public function findNotification($id): DatabaseNotification
    {
        return DatabaseNotification::findOrFail($id);
    }
    
    public function updateReadAllNotifications($user): void
    {
        DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
