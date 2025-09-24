<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Admin\OrdersController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;


require __DIR__.'/materials.php';
require __DIR__.'/products.php';
require __DIR__.'/pos.php';
require __DIR__.'/suppliers.php';

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('dashboard', function () {
    $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
    $recentOrders = Order::with(['user'])->latest()->limit(10)->get();
    $recentOrderItems = OrderItem::with(['order', 'product'])->latest()->limit(10)->get();
    $myAddresses = Auth::check()
        ? UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get()
        : collect();
    $recentPayments = Payment::with(['order'])->latest()->limit(10)->get();

    $data = [
        'message' => __('Dashboard'),
        'ordersByStatus' => $ordersByStatus,
        'totalOrders' => Order::count(),
        'itemsSold' => OrderItem::sum('qty'),
        'recentOrders' => $recentOrders,
        'recentOrderItems' => $recentOrderItems,
        'myAddresses' => $myAddresses,
        'recentPayments' => $recentPayments,
    ];
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
Volt::route('settings/password', 'settings.password')->name('password.edit');
Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
Route::view('settings/address', 'settings.address')->name('address.edit');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $recentOrders = Order::with(['user'])->latest()->limit(10)->get();
        $recentOrderItems = OrderItem::with(['order', 'product'])->latest()->limit(10)->get();
        $myAddresses = Auth::check()
            ? UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get()
            : collect();
        $recentPayments = Payment::with(['order'])->latest()->limit(10)->get();

        $data = [
            'message' => 'Admin Dashboard',
            'ordersByStatus' => $ordersByStatus,
            'totalOrders' => Order::count(),
            'itemsSold' => OrderItem::sum('qty'),
            'recentOrders' => $recentOrders,
            'recentOrderItems' => $recentOrderItems,
            'myAddresses' => $myAddresses,
            'recentPayments' => $recentPayments,
        ];
        return view('dashboard', $data);
    })->name('admin.dashboard');
    
    // Orders
    Route::get('/orders', [OrdersController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrdersController::class, 'show'])->name('admin.orders.show');
    Route::put('/orders/{order}/delivery-status', [OrdersController::class, 'updateDeliveryStatus'])->name('admin.orders.delivery.update');
});

// Staff routes
Route::middleware(['auth', 'role:staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', function () {
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $recentOrders = Order::with(['user'])->latest()->limit(10)->get();
        $recentOrderItems = OrderItem::with(['order', 'product'])->latest()->limit(10)->get();
        $myAddresses = Auth::check()
            ? UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get()
            : collect();
        $recentPayments = Payment::with(['order'])->latest()->limit(10)->get();

        $data = [
            'message' => 'Staff Dashboard',
            'ordersByStatus' => $ordersByStatus,
            'totalOrders' => Order::count(),
            'itemsSold' => OrderItem::sum('qty'),
            'recentOrders' => $recentOrders,
            'recentOrderItems' => $recentOrderItems,
            'myAddresses' => $myAddresses,
            'recentPayments' => $recentPayments,
        ];
        return view('dashboard', $data);
    })->name('staff.dashboard');
});

// Driver routes
Route::middleware(['auth', 'role:driver'])->prefix('driver')->group(function () {
    Route::get('/dashboard', function () {
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $recentOrders = Order::with(['user'])->latest()->limit(10)->get();
        $recentOrderItems = OrderItem::with(['order', 'product'])->latest()->limit(10)->get();
        $myAddresses = Auth::check()
            ? UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get()
            : collect();
        $recentPayments = Payment::with(['order'])->latest()->limit(10)->get();

        $data = [
            'message' => 'Driver Dashboard',
            'ordersByStatus' => $ordersByStatus,
            'totalOrders' => Order::count(),
            'itemsSold' => OrderItem::sum('qty'),
            'recentOrders' => $recentOrders,
            'recentOrderItems' => $recentOrderItems,
            'myAddresses' => $myAddresses,
            'recentPayments' => $recentPayments,
        ];
        return view('dashboard', $data);
    })->name('driver.dashboard');
});

// Test route for ProductRepositoryInterface
Route::get('/test-product-repository', TestController::class);

require __DIR__ . '/auth.php';
