<?php

namespace App\Providers;

use App\Interfaces\ImageRepositoryInterface;
use App\Interfaces\CommentRepositoryInterface;
use App\Interfaces\LikeRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\FeatureRepositoryInterface;
use App\Repositories\ImageRepository;
use App\Repositories\CommentRepository;
use App\Repositories\LikeRepository;
use App\Repositories\UserRepository;
use App\Repositories\FeatureRepository;
use Illuminate\Support\ServiceProvider;


class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // User
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        // Image
        $this->app->bind(ImageRepositoryInterface::class, ImageRepository::class);
        // Comment
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
        // Like
        $this->app->bind(LikeRepositoryInterface::class, LikeRepository::class);
        // Feature
        $this->app->bind(FeatureRepositoryInterface::class, FeatureRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
} 