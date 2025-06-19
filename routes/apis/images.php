<?php

use App\Http\Controllers\API\ImageController;
use Illuminate\Support\Facades\Route;

// Public Image Routes
Route::get('/get_images_information/{id}', [ImageController::class, 'getImages']);
Route::get('/get_images_by_feature/{id}', [ImageController::class, 'getImagesByFeature']);

// Protected Image Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Image upload Routes
    Route::post('/upload_images/{featureId}', [ImageController::class, 'store']);
    
    // Image Routes
    Route::get('/get_images_uploaded', [ImageController::class, 'getImagesUploaded']);
    Route::get('/get_images_liked', [ImageController::class, 'getImagesLiked']);
    Route::get('/check_new_images', [ImageController::class, 'checkNewImages']);
    Route::delete('/images/{image}', [ImageController::class, 'destroy']);
    Route::patch('/images/{image}', [ImageController::class, 'update']);
}); 