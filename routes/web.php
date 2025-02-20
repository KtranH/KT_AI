<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\API\FeatureController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// API Routes
Route::middleware(['web', 'api'])->prefix('api')->group(function () {
    
    // Auth Routes
    Route::get('/check', [AuthController::class, 'check']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
        
    // Public API Routes
    Route::get('/features', [FeatureController::class, 'load_feature']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Protected API Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

// Google OAuth Routes
Route::get('/auth/google/url', [GoogleController::class, 'redirectUrl']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleCallback']);

// SPA Routes (phải đặt cuối cùng)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');