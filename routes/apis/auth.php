<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\MailController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\API\TurnstileController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/check', [AuthController::class, 'checkStatus']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// API Testing Route - Dành cho Postman/API testing (không cần Turnstile)
Route::post('/api-login', [AuthController::class, 'apiLogin']);

Route::post('/verify-email', [MailController::class, 'verifyEmail']);
Route::post('/resend-verification', [MailController::class, 'resendVerification']);

// Forgot Password Routes
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/verify-reset-code', [ForgotPasswordController::class, 'verifyCode']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

// Turnstile Config Route - Không yêu cầu xác thực vì cần trước khi đăng nhập
Route::get('/turnstile/config', [TurnstileController::class, 'getConfig']);

// Protected Auth Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/send-password-change-verification', [MailController::class, 'sendPasswordChangeVerification']);
}); 