<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PosController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('admin.pos');

    Route::post('/pos/add/{product}', [PosController::class, 'add'])->name('admin.pos.add');
    Route::patch('/pos/increment/{product}', [PosController::class, 'increment'])->name('admin.pos.increment');
    Route::patch('/pos/decrement/{product}', [PosController::class, 'decrement'])->name('admin.pos.decrement');
    Route::delete('/pos/remove/{product}', [PosController::class, 'remove'])->name('admin.pos.remove');
    Route::delete('/pos/clear', [PosController::class, 'clear'])->name('admin.pos.clear');

    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('admin.pos.checkout');
    Route::get('/pos/receipt/{order}', [PosController::class, 'receipt'])->name('admin.pos.receipt');
    Route::get('/pos/receipt/{order}/download', [PosController::class, 'receiptDownload'])->name('admin.pos.receipt.download');
});