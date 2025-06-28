<?php

namespace App\Providers;

use App\Models\Comment;
use App\Policies\CommentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Định nghĩa các policy cho ứng dụng
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Comment::class => CommentPolicy::class,
    ];

    /**
     * Đăng ký các policy cho ứng dụng
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
} 