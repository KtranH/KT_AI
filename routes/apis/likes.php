<?php

use App\Http\Controllers\V1\Social\LikeController;
use Illuminate\Support\Facades\Route;

// Public Like Routes
Route::get('/get_likes_information/{id}', [LikeController::class, 'getLikes']);

// Protected Like Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/check_liked/{id}', [LikeController::class, 'checkLiked']);
    Route::post('/like_post/{id}', [LikeController::class, 'likePost']);
    Route::post('/unlike_post/{id}', [LikeController::class, 'unlikePost']);
}); 