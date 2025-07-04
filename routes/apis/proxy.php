<?php

use App\Http\Controllers\V1\System\ProxyController;
use Illuminate\Support\Facades\Route;

// Protected Proxy Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Proxy route cho hình ảnh
    Route::get('/proxy/r2-image', [ProxyController::class, 'proxyR2Image']);
    Route::get('/download/r2-image', [ProxyController::class, 'downloadR2Image']);
    Route::get('/r2-download', [ProxyController::class, 'downloadFromR2Storage']);
}); 