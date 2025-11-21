<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SizeController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('sizes', SizeController::class)->names('admin.sizes');
});

// Shared route for fetching sizes by category (admin and staff)
Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::get('/sizes/by-category/{category}', [SizeController::class, 'byCategory'])->name('sizes.by-category');
});