<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Default user route
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Include API route files
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