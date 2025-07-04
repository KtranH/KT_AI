<?php

use App\Http\Controllers\V1\Content\ImageJobController;
use Illuminate\Support\Facades\Route;

// Protected Image Jobs Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/image-jobs/create', [ImageJobController::class, 'create']);
    Route::get('/image-jobs/active', [ImageJobController::class, 'getActiveJobs']);
    Route::get('/image-jobs/completed', [ImageJobController::class, 'getCompletedJobs']);
    Route::get('/image-jobs/failed', [ImageJobController::class, 'getFailedJobs']);
    Route::get('/image-jobs/{jobId}', [ImageJobController::class, 'checkJobStatus']);
    Route::delete('/image-jobs/{jobId}', [ImageJobController::class, 'cancelJob']);
    Route::post('/image-jobs/{jobId}/retry', [ImageJobController::class, 'retryJob']);
}); 