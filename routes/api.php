<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;

Route::prefix('auth')->group(function () {
    Route::get('check', [AuthController::class, 'check']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth');
    
    // Google OAuth routes
    Route::get('google/url', [GoogleController::class, 'redirectUrl']);
    Route::post('google/callback', [GoogleController::class, 'handleCallback']);
}); 