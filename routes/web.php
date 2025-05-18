<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\MailController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\API\FeatureController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TurnstileController;
use App\Http\Controllers\API\ImageController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\LikeController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\StatisticsController;
use App\Http\Controllers\API\ImageJobController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Thêm route cho xác thực kênh broadcast
Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

// API Routes
Route::prefix('api')->group(function () {

    // Auth Routes
    Route::get('/check', [AuthController::class, 'checkStatus']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [MailController::class, 'verifyEmail']);
    Route::post('/resend-verification', [MailController::class, 'resendVerification']);

    // Forgot Password Routes
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('/verify-reset-code', [ForgotPasswordController::class, 'verifyCode']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

    // Public API Routes
    Route::get('/load_features', [FeatureController::class, 'getFeatures']);
    Route::get('/load_features/{id}', [FeatureController::class, 'getFeatureById']);
    Route::get('/turnstile/config', [TurnstileController::class, 'getConfig']);
    Route::get('/get_images_information/{id}', [ImageController::class, 'getImages']);
    Route::get('/get_images_by_feature/{id}', [ImageController::class, 'getImagesByFeature']);
    // Public API Get Like
    Route::get('/get_likes_information/{id}', [LikeController::class, 'getLikes']);

    // Protected API Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthController::class, 'logout']);
        // Like Routes
        Route::get('/check_liked/{id}', [LikeController::class, 'checkLiked']);
        Route::post('/like_post/{id}', [LikeController::class, 'likePost']);
        Route::post('/unlike_post/{id}', [LikeController::class, 'unlikePost']);
        // Comment Routes
        Route::get('/images/{imageId}/comments', [CommentController::class, 'getComments']);
        Route::post('/comments', [CommentController::class, 'store']);
        Route::put('/comments/{comment}', [CommentController::class, 'update']);
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
        Route::post('/comments/{comment}/toggle-like', [CommentController::class, 'toggleLike']);
        // Reply Routes
        Route::post('/comments/{comment}/reply', [CommentController::class, 'storeReply']);
        // Image upload Routes
        Route::post('/upload_images/{featureId}', [ImageController::class, 'store']);
        // Image Routes
        Route::get('/get_images_created_by_user', [ImageController::class, 'getImagesCreatedByUser']);
        Route::get('/get_images_uploaded', [ImageController::class, 'getImagesUploaded']);
        Route::get('/get_images_liked', [ImageController::class, 'getImagesLiked']);
        Route::delete('/images/{image}', [ImageController::class, 'destroy']);
        Route::put('/images/{image}', [ImageController::class, 'update']);
        // Profile Routes
        Route::post('/update-avatar', [UserController::class, 'updateAvatar']);
        Route::post('/update-cover-image', [UserController::class, 'updateCoverImage']);
        Route::post('/update-name', [UserController::class, 'updateName']);
        Route::post('/send-password-change-verification', [MailController::class, 'sendPasswordChangeVerification']);
        Route::post('/update-password', [UserController::class, 'updatePassword']);
        Route::post('/check-password', [UserController::class, 'checkPassword']);
        // Routes Notification
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        // Statistics Route
        Route::get('/statistics', [StatisticsController::class, 'getStatistics']);
        // Image Jobs API Routes
        Route::post('/image-jobs/create', [ImageJobController::class, 'create']);
        Route::get('/image-jobs/active', [ImageJobController::class, 'getActiveJobs']);
        Route::get('/image-jobs/completed', [ImageJobController::class, 'getCompletedJobs']);
        Route::get('/image-jobs/failed', [ImageJobController::class, 'getFailedJobs']);
        Route::get('/image-jobs/{jobId}', [ImageJobController::class, 'checkJobStatus']);
        Route::delete('/image-jobs/{jobId}', [ImageJobController::class, 'cancelJob']);
        Route::post('/image-jobs/{jobId}/retry', [ImageJobController::class, 'retryJob']);
    });
});

// Google OAuth Routes
Route::get('/auth/google/url', [GoogleController::class, 'redirectUrl']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleCallback']);

//Test redis
Route::get('/test/redis', function() {
    try {
        $ping = Redis::connection()->ping();
        return response()->json([
            'status' => 'success',
            'message' => 'Kết nối thành công',
            'ping' => $ping,
            'set_value' => Redis::set('test', 'test'),
            'get_value' => Redis::get('test'),
        ]);
    } catch(\Exception $e) {
        report($e); // Ghi log lỗi vào storage/logs/laravel.log
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});


// SPA Routes (phải đặt cuối cùng)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
