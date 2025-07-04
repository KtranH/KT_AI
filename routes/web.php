<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\Auth\GoogleAuthController;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Thêm route cho xác thực kênh broadcast
Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

// Google OAuth Callback Route (phải là web route)
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleCallback']);

// SPA Routes
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
