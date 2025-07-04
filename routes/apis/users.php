<?php

use App\Http\Controllers\V1\User\ProfileController;
use Illuminate\Support\Facades\Route;

// Protected User Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/{id}', [ProfileController::class, 'getUserById']);
    
    // Profile Routes
    Route::post('/update-avatar', [ProfileController::class, 'updateAvatar']);
    Route::post('/update-cover-image', [ProfileController::class, 'updateCoverImage']);
    Route::patch('/update-name', [ProfileController::class, 'updateName']);
    Route::patch('/update-password', [ProfileController::class, 'updatePassword']);
    Route::post('/check-password', [ProfileController::class, 'checkPassword']);
}); 