<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\API\FeatureController;
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
        
    // Google OAuth Routes
    Route::get('/google/url', [AuthController::class, 'getGoogleAuthUrl']);
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback']);
    
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

// SPA Routes (phải đặt cuối cùng)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');