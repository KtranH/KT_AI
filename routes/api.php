<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Api\TurnstileController;

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