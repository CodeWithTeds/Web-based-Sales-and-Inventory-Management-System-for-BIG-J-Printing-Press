<?php

use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    // Standard CRUD routes
    Route::resource('materials', MaterialController::class);

    // Custom routes for stock management
    Route::get('/materials/{material}/stock-in', [MaterialController::class, 'showStockInForm'])->name('materials.stock-in.form');
    Route::post('/materials/{material}/stock-in', [MaterialController::class, 'stockIn'])->name('materials.stock-in');

    // Material request routes
    Route::get('/materials/request/form', [MaterialController::class, 'showRequestForm'])->name('materials.request.form');
    Route::post('/materials/request/submit', [MaterialController::class, 'submitRequest'])->name('materials.submit-request');

    // Reports and filters
    Route::get('/materials/reports/low-stock', [MaterialController::class, 'lowStock'])->name('materials.low-stock');
    Route::get('/materials/filter/category', [MaterialController::class, 'byCategory'])->name('materials.by-category');
});
