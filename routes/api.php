<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Api\TurnstileController;
use App\Http\Controllers\CommentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Google OAuth Routes
Route::get('/google/url', [GoogleController::class, 'redirectUrl']);
Route::get('/google/callback', [GoogleController::class, 'handleCallback']);

// Turnstile Config Route - Không yêu cầu xác thực vì cần trước khi đăng nhập
Route::get('/turnstile/config', [TurnstileController::class, 'getConfig']);

// Routes cho bình luận
Route::middleware('auth:sanctum')->group(function () {
    // Lấy bình luận cho một hình ảnh
    Route::get('/images/{imageId}/comments', [App\Http\Controllers\API\CommentController::class, 'getComments']);
    
    // Tạo bình luận mới
    Route::post('/comments', [CommentController::class, 'store']);
    
    // Cập nhật bình luận
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    
    // Xóa bình luận
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
    // Thích hoặc bỏ thích bình luận
    Route::post('/comments/{comment}/toggle-like', [CommentController::class, 'toggleLike']);
}); 