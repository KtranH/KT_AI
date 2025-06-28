<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Định nghĩa các event và listener cho ứng dụng
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Đăng ký các event và listener cho ứng dụng
     */
    public function boot(): void
    {
        //
    }

    /**
     * Xác định xem các event và listener có được tự động phát hiện không
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
} 