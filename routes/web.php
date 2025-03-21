<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\API\FeatureController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TurnstileController;
use App\Http\Controllers\API\ImageController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// API Routes
Route::prefix('api')->group(function () {
    
    // Auth Routes
    Route::get('/check', [AuthController::class, 'check']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
        
    // Public API Routes
    Route::get('/load_features', [FeatureController::class, 'load_feature']);
    Route::get('/load_features/{id}', [FeatureController::class, 'get_feature']);
    Route::get('/turnstile/config', [TurnstileController::class, 'getConfig']); 
    Route::get('/get_images_information/{id}', [ImageController::class, 'getImages']);
    Route::get('/get_images_created_by_user', [ImageController::class, 'getImagesCreatedByUser']);

    // Protected API Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/logout', [AuthController::class, 'logout']);
    });
    // Tránh trùng lặp route logout
    // Route::post('/logout', [AuthController::class, 'logout']);
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
