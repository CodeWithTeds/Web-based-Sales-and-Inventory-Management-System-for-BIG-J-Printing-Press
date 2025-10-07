<?php

use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    // Standard CRUD routes (excluding destroy; delete is admin-only)
    Route::resource('materials', MaterialController::class)->except(['destroy']);

    // Custom routes for stock management
    Route::get('/materials/{material}/stock-in', [MaterialController::class, 'showStockInForm'])->name('materials.stock-in.form');
    Route::post('/materials/{material}/stock-in', [MaterialController::class, 'stockIn'])->name('materials.stock-in');

    // New: stock-out
    Route::get('/materials/{material}/stock-out', [MaterialController::class, 'showStockOutForm'])->name('materials.stock-out.form');
    Route::post('/materials/{material}/stock-out', [MaterialController::class, 'stockOut'])->name('materials.stock-out');

    // Material request routes
    Route::get('/materials/request/form', [MaterialController::class, 'showRequestForm'])->name('materials.request.form');
    Route::post('/materials/request/submit', [MaterialController::class, 'submitRequest'])->name('materials.submit-request');

    // Reports and filters
    Route::get('/materials/reports/low-stock', [MaterialController::class, 'lowStock'])->name('materials.low-stock');
    Route::get('/materials/filter/category', [MaterialController::class, 'byCategory'])->name('materials.by-category');
});

// Admin-only route for deleting materials
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
});
