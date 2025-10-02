<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OutstandingBalancesController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // Outstanding Balances index
    Route::get('outstanding-balances', [OutstandingBalancesController::class, 'index'])->name('admin.outstanding-balances.index');

    // CRUD for payments linked to orders
    Route::get('outstanding-balances/{order}/payments/create', [OutstandingBalancesController::class, 'createPayment'])->name('admin.outstanding-balances.payments.create');
    Route::post('outstanding-balances/{order}/payments', [OutstandingBalancesController::class, 'storePayment'])->name('admin.outstanding-balances.payments.store');

    Route::get('outstanding-balances/payments/{payment}/edit', [OutstandingBalancesController::class, 'editPayment'])->name('admin.outstanding-balances.payments.edit');
    Route::put('outstanding-balances/payments/{payment}', [OutstandingBalancesController::class, 'updatePayment'])->name('admin.outstanding-balances.payments.update');
    Route::delete('outstanding-balances/payments/{payment}', [OutstandingBalancesController::class, 'destroyPayment'])->name('admin.outstanding-balances.payments.destroy');

    // Send balance reminder email
    Route::post('outstanding-balances/{order}/send-reminder', [OutstandingBalancesController::class, 'sendReminder'])->name('admin.outstanding-balances.reminder');
});