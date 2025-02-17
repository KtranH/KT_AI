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
Route::middleware(['api'])->prefix('api')->group(function () {
    // Public API Routes
    Route::get('/features', [FeatureController::class, 'load_feature']);
    
    // Auth Routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Protected API Routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

// SPA Routes (phải đặt cuối cùng)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');