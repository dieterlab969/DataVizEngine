<?php

use App\Http\Controllers\Api\WikipediaController;
use Illuminate\Support\Facades\Route;

Route::post('/extract-table', [WikipediaController::class, 'extractTable']);
Route::post('/generate-visualization', [WikipediaController::class, 'generateVisualization']);
