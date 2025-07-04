<?php

use App\Http\Controllers\V1\Content\FeatureController;
use Illuminate\Support\Facades\Route;

// Public Feature Routes
Route::get('/load_features', [FeatureController::class, 'getFeatures']);
Route::get('/load_features/{id}', [FeatureController::class, 'getFeatureById']); 