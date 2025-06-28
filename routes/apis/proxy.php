<?php

use Illuminate\Support\Facades\Route;

// Protected Proxy Routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Proxy route cho hình ảnh
    Route::get('/proxy/r2-image', 'App\Http\Controllers\API\ProxyController@proxyR2Image');
    Route::get('/download/r2-image', 'App\Http\Controllers\API\ProxyController@downloadR2Image');
    Route::get('/r2-download', 'App\Http\Controllers\API\ProxyController@downloadFromR2Storage');
}); 