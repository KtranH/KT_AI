<?php

use App\Http\Controllers\V1\System\StatisticsController;
use Illuminate\Support\Facades\Route;

// Protected Statistics Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/statistics', [StatisticsController::class, 'getStatistics']);
    Route::get('/user-statistics', [StatisticsController::class, 'getUserStatistics']);
}); 