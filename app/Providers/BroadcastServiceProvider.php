<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Lưu ý: Broadcast::routes đã được đăng ký trong file routes/web.php
        // với middleware ['web', 'auth:sanctum']
        
        require base_path('routes/channels.php');
    }
} 