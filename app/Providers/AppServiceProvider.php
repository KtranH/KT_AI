<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Services\ComfyUITemplateService;
use App\Services\ComfyUIApiService;
use App\Services\ComfyUIJobService;
use App\Interfaces\ImageJobRepositoryInterface;
use App\Repositories\ImageJobsRepository;
use App\Models\Image;
use App\Models\Comment;
use App\Models\Interaction;
use App\Observers\ImageObserver;
use App\Observers\CommentObserver;
use App\Observers\InteractionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký các service cho ứng dụng
     */
    public function register(): void
    {
        // Đăng ký các service cho ComfyUI
        $this->app->singleton(ComfyUITemplateService::class, function ($app) {
            return new ComfyUITemplateService(
                $app->make(ComfyUIApiService::class)
            );
        });
        
        $this->app->singleton(ComfyUIApiService::class, function ($app) {
            return new ComfyUIApiService();
        });
        
        $this->app->singleton(ComfyUIJobService::class, function ($app) {
            return new ComfyUIJobService(
                $app->make(ComfyUIApiService::class)
            );
        });

        // Đăng ký binding cho ImageJob
        $this->app->bind(ImageJobRepositoryInterface::class, ImageJobsRepository::class);
    }

    /**
     * Khởi tạo các service cho ứng dụng
     */
    public function boot(): void
    {
        // Đảm bảo các commands được đăng ký trong Laravel 11
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\StartQueueWorker::class,
                \App\Console\Commands\MakeSupervisorConfig::class,
            ]);
        }

        //
        Carbon::setLocale('vi');

        // Đăng ký Observer cho Image
        Image::observe(ImageObserver::class);
        
        // Đăng ký Observer cho Comment
        Comment::observe(CommentObserver::class);
        
        // Đăng ký Observer cho Interaction
        Interaction::observe(InteractionObserver::class);
    }
}
