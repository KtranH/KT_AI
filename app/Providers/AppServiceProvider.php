<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Services\ComfyUITemplateService;
use App\Services\ComfyUIApiService;
use App\Services\ComfyUIJobService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Đăng ký các service cho ComfyUI
        $this->app->singleton(ComfyUITemplateService::class, function ($app) {
            return new ComfyUITemplateService();
        });
        
        $this->app->singleton(ComfyUIApiService::class, function ($app) {
            return new ComfyUIApiService();
        });
        
        $this->app->singleton(ComfyUIJobService::class, function ($app) {
            return new ComfyUIJobService(
                $app->make(ComfyUIApiService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
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
    }
}
