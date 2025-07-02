<?php

use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

// Protected User Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/{id}', [UserController::class, 'getUserById']);
    
    // Profile Routes
    Route::post('/update-avatar', [UserController::class, 'updateAvatar']);
    Route::post('/update-cover-image', [UserController::class, 'updateCoverImage']);
    Route::patch('/update-name', [UserController::class, 'updateName']);
    Route::patch('/update-password', [UserController::class, 'updatePassword']);
    Route::post('/check-password', [UserController::class, 'checkPassword']);
}); 