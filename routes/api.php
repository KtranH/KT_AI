<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\TurnstileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\API\StatisticsController;

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

// Forgot Password Routes
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/verify-reset-code', [ForgotPasswordController::class, 'verifyCode']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

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
    
    // Routes cho thông báo - sử dụng controller mới
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);

    // Statistics Route
    Route::get('/statistics', [StatisticsController::class, 'getStatistics']);

    // Image Jobs API Routes
    Route::post('/image-jobs/create', 'App\Http\Controllers\API\ImageJobController@create');
    Route::get('/image-jobs/active', 'App\Http\Controllers\API\ImageJobController@getActiveJobs');
    Route::get('/image-jobs/completed', 'App\Http\Controllers\API\ImageJobController@getCompletedJobs');
    Route::get('/image-jobs/failed', 'App\Http\Controllers\API\ImageJobController@getFailedJobs');
    Route::get('/image-jobs/{jobId}', 'App\Http\Controllers\API\ImageJobController@checkJobStatus');
    Route::delete('/image-jobs/{jobId}', 'App\Http\Controllers\API\ImageJobController@cancelJob');
    Route::post('/image-jobs/{jobId}/retry', 'App\Http\Controllers\API\ImageJobController@retryJob');

    // Proxy route cho hình ảnh
    Route::get('/proxy/r2-image', 'App\Http\Controllers\API\ProxyController@proxyR2Image');
    Route::get('/download/r2-image', 'App\Http\Controllers\API\ProxyController@downloadR2Image');
    Route::get('/r2-download', 'App\Http\Controllers\API\ProxyController@downloadFromR2Storage');
});