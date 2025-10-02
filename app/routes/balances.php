<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BalancesController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Balances index: show outstanding balances per order
    Route::get('balances', [BalancesController::class, 'index'])->name('admin.balances.index');

    // CRUD for payments linked to orders
    Route::get('balances/{order}/payments/create', [BalancesController::class, 'createPayment'])->name('admin.balances.payments.create');
    Route::post('balances/{order}/payments', [BalancesController::class, 'storePayment'])->name('admin.balances.payments.store');

    Route::get('balances/payments/{payment}/edit', [BalancesController::class, 'editPayment'])->name('admin.balances.payments.edit');
    Route::put('balances/payments/{payment}', [BalancesController::class, 'updatePayment'])->name('admin.balances.payments.update');
    Route::delete('balances/payments/{payment}', [BalancesController::class, 'destroyPayment'])->name('admin.balances.payments.destroy');

    // Send balance reminder email
    Route::post('balances/{order}/send-reminder', [BalancesController::class, 'sendReminder'])->name('admin.balances.reminder');
});