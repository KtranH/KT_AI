<?php

use App\Http\Controllers\V1\Auth\AuthController;
use App\Http\Controllers\V1\Auth\EmailVerificationController;
use App\Http\Controllers\V1\Auth\PasswordController;
use App\Http\Controllers\V1\System\TurnstileController;
use App\Http\Controllers\V1\Auth\GoogleAuthController;
use App\Http\Controllers\V1\Auth\Google2FAController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/check', [AuthController::class, 'checkStatus']);
Route::post('/login', [AuthController::class, 'login'])->middleware('web')->name('login');    
Route::post('/register', [AuthController::class, 'register'])->middleware('web')->name('register');

// API Testing Route - Dành cho Postman/API testing (không cần Turnstile)
Route::post('/api-login', [AuthController::class, 'apiLogin']);

Route::post('/verify-email', [EmailVerificationController::class, 'verify']);
Route::post('/resend-verification', [EmailVerificationController::class, 'resendVerificationCode']);

// Forgot Password Routes
Route::post('/forgot-password', [PasswordController::class, 'sendResetLinkEmail']);
Route::post('/verify-reset-code', [PasswordController::class, 'verifyCode']);
Route::post('/reset-password', [PasswordController::class, 'reset']);

// Turnstile Config Route - Không yêu cầu xác thực vì cần trước khi đăng nhập
Route::get('/turnstile/config', [TurnstileController::class, 'getConfig']);

// Google OAuth Routes
Route::get('/google/url', [GoogleAuthController::class, 'getRedirectUrl'])->middleware('web');
Route::get('/google/callback', [GoogleAuthController::class, 'handleCallback'])->middleware('web');

// Protected Auth Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Google2FA Routes
    Route::prefix('2fa')->group(function () {
        Route::get('/status', [Google2FAController::class, 'getStatus']);
        Route::post('/generate', [Google2FAController::class, 'generateQRCode']);
        Route::post('/enable', [Google2FAController::class, 'enable']);
        Route::post('/disable', [Google2FAController::class, 'disable']);
        Route::post('/recovery-codes', [Google2FAController::class, 'generateRecoveryCodes']);
    });
}); 

// Public 2FA Routes (không cần auth)
Route::prefix('2fa')->group(function () {
    Route::post('/verify', [Google2FAController::class, 'verify']);
    Route::post('/recovery', [Google2FAController::class, 'useRecoveryCode']);
    Route::post('/verify-login', [Google2FAController::class, 'verifyLogin2FA']);
    
});