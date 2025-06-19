<?php

use App\Http\Controllers\API\StatisticsController;
use Illuminate\Support\Facades\Route;

// Protected Statistics Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/statistics', [StatisticsController::class, 'getStatistics']);
}); 