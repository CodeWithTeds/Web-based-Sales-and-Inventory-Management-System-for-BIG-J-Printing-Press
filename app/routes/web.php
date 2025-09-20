<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\TestController;

// Include materials and products routes
require __DIR__.'/materials.php';
require __DIR__.'/products.php';

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['message' => 'Admin Dashboard']);
    })->name('admin.dashboard');

    // POS page
    Route::view('/pos', 'admin.pos')->name('admin.pos');
});

// Staff routes
Route::middleware(['auth', 'role:staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['message' => 'Staff Dashboard']);
    })->name('staff.dashboard');
});

// Driver routes
Route::middleware(['auth', 'role:driver'])->prefix('driver')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['message' => 'Driver Dashboard']);
    })->name('driver.dashboard');
});

// Test route for ProductRepositoryInterface
Route::get('/test-product-repository', TestController::class);

require __DIR__ . '/auth.php';
