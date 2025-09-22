<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    // Standard CRUD routes
    Route::resource('products', ProductController::class);

    // Custom routes for category filtering
    Route::get('/products/filter/category/{category?}', [ProductController::class, 'byCategory'])->name('products.by-category');

    // Material management for products
    Route::get('/products/{product}/materials', [ProductController::class, 'showMaterialsForm'])->name('products.materials.form');
    Route::post('/products/{product}/materials', [ProductController::class, 'addMaterial'])->name('products.materials.add');
    Route::delete('/products/{product}/materials/{material}', [ProductController::class, 'removeMaterial'])->name('products.materials.remove');
});