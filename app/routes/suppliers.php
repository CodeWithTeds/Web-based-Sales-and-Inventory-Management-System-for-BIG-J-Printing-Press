<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('suppliers', SupplierController::class)->names('admin.suppliers');
});