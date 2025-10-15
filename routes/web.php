<?php

use App\Http\Controllers\PageRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('page_requests.index');
});

Route::resource('page_requests', PageRequestController::class)->only([
    'index', 'create', 'store', 'show'
]);

Route::post('/page_requests/{id}/retry', [
    PageRequestController::class, 'retryVisualization'
])->name('page_requests.retryVisualization');
