<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DriverController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('drivers', DriverController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
        ->names('admin.drivers');
});