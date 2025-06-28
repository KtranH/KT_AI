<?php

use App\Http\Controllers\API\CommentController;
use Illuminate\Support\Facades\Route;

// Protected Comment Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Comment Routes
    Route::get('/images/{imageId}/comments', [CommentController::class, 'getComments']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('/comments/{comment}/toggle-like', [CommentController::class, 'toggleLike']);
    
    // Reply Routes
    Route::post('/comments/{comment}/reply', [CommentController::class, 'storeReply']);
}); 