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

// Client-facing Online Ordering (reuses POS)
Route::middleware(['auth', 'role:client'])->group(function () {
    // Base page
    Route::get('/ordering', [PosController::class, 'index'])->name('client.ordering');

    // Cart operations
    Route::post('/ordering/add/{product}', [PosController::class, 'add'])->name('client.ordering.add');
    Route::patch('/ordering/increment/{product}', [PosController::class, 'increment'])->name('client.ordering.increment');
    Route::patch('/ordering/decrement/{product}', [PosController::class, 'decrement'])->name('client.ordering.decrement');
    Route::delete('/ordering/remove/{product}', [PosController::class, 'remove'])->name('client.ordering.remove');
    Route::delete('/ordering/clear', [PosController::class, 'clear'])->name('client.ordering.clear');

    // Checkout & receipt
    Route::post('/ordering/checkout', [PosController::class, 'checkout'])->name('client.ordering.checkout');
    
    // PayMongo callbacks (no webhooks)
    Route::get('/ordering/paymongo/success', [PosController::class, 'paymongoSuccess'])->name('client.ordering.paymongo.success');
    Route::get('/ordering/paymongo/cancel', [PosController::class, 'paymongoCancel'])->name('client.ordering.paymongo.cancel');

    Route::get('/ordering/receipt/{order}', [PosController::class, 'receipt'])->name('client.ordering.receipt');
    Route::get('/ordering/receipt/{order}/download', [PosController::class, 'receiptDownload'])->name('client.ordering.receipt.download');
});