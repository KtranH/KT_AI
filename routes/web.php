<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Thêm route cho xác thực kênh broadcast
Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

// Google OAuth Callback Route (Web route for browser redirect)
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
