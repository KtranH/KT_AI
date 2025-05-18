<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
