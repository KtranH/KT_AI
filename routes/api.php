<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Đây là nơi để bạn định nghĩa các route cho api
| Các route được tự động load bởi RouteServiceProvider
| và tất cả các route đều được gán vào middleware group "api"
|
*/

// Mặc định là route cho user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Bắt đầu định nghĩa các route cho api
require __DIR__ . '/apis/auth.php';
require __DIR__ . '/apis/features.php';
require __DIR__ . '/apis/images.php';
require __DIR__ . '/apis/users.php';
require __DIR__ . '/apis/comments.php';
require __DIR__ . '/apis/likes.php';
require __DIR__ . '/apis/notifications.php';
require __DIR__ . '/apis/statistics.php';
require __DIR__ . '/apis/image-jobs.php';
require __DIR__ . '/apis/proxy.php';