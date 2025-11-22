<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\PhysicalInventoryController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\UserAddress;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


require __DIR__ . '/materials.php';
require __DIR__ . '/products.php';
require __DIR__ . '/pos.php';
require __DIR__ . '/categories.php';
require __DIR__ . '/sizes.php';
require __DIR__ . '/suppliers.php';
require __DIR__ . '/drivers.php';
require __DIR__ . '/balances.php';
require __DIR__ . '/outstanding-balances.php';

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('dashboard', function () {
    /** @var User|null $user */
    $user = Auth::user();

    // Use global counts for admins and staff; per-user counts for clients
    $deliveryByStatus = ($user && ($user->isAdmin() || $user->isStaff()))
        ? Order::selectRaw('delivery_status, COUNT(*) as count')
            ->groupBy('delivery_status')
            ->pluck('count', 'delivery_status')
            ->toArray()
        : Order::where('user_id', Auth::id())
            ->selectRaw('delivery_status, COUNT(*) as count')
            ->groupBy('delivery_status')
            ->pluck('count', 'delivery_status')
            ->toArray();

    $ordersByStatus = ($user && ($user->isAdmin() || $user->isStaff()))
        ? Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray()
        : Order::where('user_id', Auth::id())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

    $recentOrders = Order::where('user_id', Auth::id())
        ->latest()->limit(10)->get();

    $recentOrderItems = OrderItem::with(['order', 'product'])
        ->whereHas('order', function ($oq) {
            $oq->where('user_id', Auth::id());
        })
        ->latest()->limit(10)->get();

    $myAddresses = Auth::check()
        ? ($user && $user->isAdmin()
            ? UserAddress::with('user')->latest()->limit(10)->get()
            : UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get())
        : collect();

    $recentPayments = Payment::with(['order'])
        ->when($user && !($user->isAdmin() || $user->isStaff()), function ($query) {
            $query->whereHas('order', function ($oq) {
                $oq->where('user_id', Auth::id());
            });
        })
        ->latest()->limit(10)->get();

    // Build 30-day sales chart data (exclude POS downpayment records)
    $start = now()->subDays(29)->startOfDay();
    $labels = [];
    $series = [];
    for ($i = 0; $i < 30; $i++) {
        $labels[] = $start->copy()->addDays($i)->format('Y-m-d');
        $series[] = 0.0;
    }
    $paymentsForChart = Payment::with('order')
        ->when($user && !($user->isAdmin() || $user->isStaff()), function ($query) {
            $query->whereHas('order', function ($oq) {
                $oq->where('user_id', Auth::id());
            });
        })
        ->where(function($q){ $q->whereNull('reference')->orWhere('reference','not like','POSDP-%'); })
        ->where(function($q) use ($start){
            $q->where('created_at', '>=', $start)->orWhere('paid_at', '>=', $start);
        })
        ->get();
    foreach ($paymentsForChart as $p) {
        $d = optional($p->paid_at ?? $p->created_at)->format('Y-m-d');
        $idx = array_search($d, $labels, true);
        if ($idx !== false) { $series[$idx] += (float) ($p->amount ?? 0); }
    }

    // Top products/materials in last 30 days
    $items = \App\Models\OrderItem::with(['product.materials'])
        ->where('created_at', '>=', $start)
        ->get();
    $agg = [];
    foreach ($items as $it) {
        $key = $it->product_id ?: ('NAME:' . ($it->name ?? 'Unknown'));
        if (!isset($agg[$key])) {
            $productName = $it->product->name ?? $it->name ?? 'Unknown';
            $materials = $it->product ? $it->product->materials->pluck('name')->implode(', ') : '—';
            $agg[$key] = [
                'product_name' => $productName,
                'materials' => $materials,
                'total_qty' => 0,
                'total_amount' => 0.0,
            ];
        }
        $agg[$key]['total_qty'] += (int) ($it->qty ?? 0);
        $agg[$key]['total_amount'] += (float) ($it->line_total ?? 0);
    }
    // Sort by total_amount desc and take top 10
    uasort($agg, fn($a, $b) => $b['total_amount'] <=> $a['total_amount']);
    $topProducts = array_slice(array_values($agg), 0, 10);

    $data = [
        'message' => __('Dashboard'),
        'deliveryByStatus' => $deliveryByStatus,
        'ordersByStatus' => $ordersByStatus,
        'totalOrders' => $user && ($user->isAdmin() || $user->isStaff()) ? Order::count() : Order::where('user_id', Auth::id())->count(),
        'itemsSold' => $user && ($user->isAdmin() || $user->isStaff()) ? OrderItem::sum('qty') : OrderItem::whereHas('order', function ($q) {
            $q->where('user_id', Auth::id());
        })->sum('qty'),
        'recentOrders' => $recentOrders,
        'recentOrderItems' => $recentOrderItems,
        'myAddresses' => $myAddresses,
        'recentPayments' => $recentPayments,
        'salesChartLabels' => $labels,
        'salesChartData' => $series,
        'topProducts' => $topProducts,
    ];
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Route::view('settings/address', 'settings.address')->middleware(['auth','role:client'])->name('address.edit');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        /** @var User|null $user */
        $user = Auth::user();

        $deliveryByStatus = Order::selectRaw('delivery_status, COUNT(*) as count')->groupBy('delivery_status')->pluck('count', 'delivery_status')->toArray();
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $recentOrders = Order::with(['user'])->latest()->limit(10)->get();
        $recentOrderItems = OrderItem::with(['order', 'product'])->latest()->limit(10)->get();
        $myAddresses = Auth::check()
            ? UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get()
            : collect();
        $recentPayments = Payment::with(['order'])->latest()->limit(10)->get();

        // Build 30-day sales chart data (exclude POS downpayment records)
        $start = now()->subDays(29)->startOfDay();
        $labels = [];
        $series = [];
        for ($i = 0; $i < 30; $i++) {
            $labels[] = $start->copy()->addDays($i)->format('Y-m-d');
            $series[] = 0.0;
        }
        $paymentsForChart = Payment::where(function($q){ $q->whereNull('reference')->orWhere('reference','not like','POSDP-%'); })
            ->where(function($q) use ($start){
                $q->where('created_at', '>=', $start)->orWhere('paid_at', '>=', $start);
            })
            ->get();
        foreach ($paymentsForChart as $p) {
            $d = optional($p->paid_at ?? $p->created_at)->format('Y-m-d');
            $idx = array_search($d, $labels, true);
            if ($idx !== false) { $series[$idx] += (float) ($p->amount ?? 0); }
        }

        // Top products/materials in last 30 days (admin)
        $items = \App\Models\OrderItem::with(['product.materials'])
            ->where('created_at', '>=', $start)
            ->get();
        $agg = [];
        foreach ($items as $it) {
            $key = $it->product_id ?: ('NAME:' . ($it->name ?? 'Unknown'));
            if (!isset($agg[$key])) {
                $productName = $it->product->name ?? $it->name ?? 'Unknown';
                $materials = $it->product ? $it->product->materials->pluck('name')->implode(', ') : '—';
                $agg[$key] = [
                    'product_name' => $productName,
                    'materials' => $materials,
                    'total_qty' => 0,
                    'total_amount' => 0.0,
                ];
            }
            $agg[$key]['total_qty'] += (int) ($it->qty ?? 0);
            $agg[$key]['total_amount'] += (float) ($it->line_total ?? 0);
        }
        uasort($agg, fn($a, $b) => $b['total_amount'] <=> $a['total_amount']);
        $topProducts = array_slice(array_values($agg), 0, 10);

        $data = [
            'message' => 'Admin Dashboard',
            'deliveryByStatus' => $deliveryByStatus,
            'ordersByStatus' => $ordersByStatus,
            'totalOrders' => Order::count(),
            'itemsSold' => OrderItem::sum('qty'),
            'recentOrders' => $recentOrders,
            'recentOrderItems' => $recentOrderItems,
            'myAddresses' => $myAddresses,
            'recentPayments' => $recentPayments,
            'salesChartLabels' => $labels,
            'salesChartData' => $series,
            'topProducts' => $topProducts,
        ];
        return view('dashboard', $data);
    })->name('admin.dashboard');

    // Sales report export (CSV)
    Route::get('/reports/sales/export', [\App\Http\Controllers\Admin\ReportsController::class, 'export'])->name('admin.reports.sales.export');

    // Orders
    Route::get('/orders', [OrdersController::class, 'index'])->name('admin.orders.index');
    Route::get('/orders/{order}', [OrdersController::class, 'show'])->name('admin.orders.show');
    Route::put('/orders/{order}/delivery-status', [OrdersController::class, 'updateDeliveryStatus'])->name('admin.orders.delivery.update');

    // Staff management: Staff list
    Route::get('/staff', function () {
        $items = \App\Models\User::where('role', 'staff')->orderBy('name')->paginate(15);
        $metrics = [
            'total' => $items->total(),
        ];
        return view('admin.staff.index', compact('items', 'metrics'));
    })->name('admin.staff.index');

    // Staff management: Create staff user
    Route::get('/staff/create', function () {
        return view('admin.staff.create');
    })->name('admin.staff.create');

    Route::post('/staff', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'staff',
        ]);

        return redirect()->route('admin.staff.create')->with('status', 'Staff user created successfully.');
    })->name('admin.staff.store');

    // Activity Logs (admin can view all)
    Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-logs.index');

    // Physical Inventory
    Route::get('/physical-inventory', [PhysicalInventoryController::class, 'index'])->name('admin.physical-inventory.index');
    Route::post('/physical-inventory/{product}', [PhysicalInventoryController::class, 'update'])->name('admin.physical-inventory.update');

    // Materials Physical Inventory
    Route::get('/materials-physical-inventory', [\App\Http\Controllers\Admin\MaterialPhysicalInventoryController::class, 'index'])->name('admin.materials-physical-inventory.index');
    Route::post('/materials-physical-inventory/{material}', [\App\Http\Controllers\Admin\MaterialPhysicalInventoryController::class, 'update'])->name('admin.materials-physical-inventory.update');
});

// Staff routes
Route::middleware(['auth', 'verified', 'role:staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', function () {
        /** @var User|null $user */
        $user = Auth::user();

        $deliveryByStatus = Order::selectRaw('delivery_status, COUNT(*) as count')->groupBy('delivery_status')->pluck('count', 'delivery_status')->toArray();
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $recentOrders = Order::with(['user'])->latest()->limit(10)->get();
        $recentOrderItems = OrderItem::with(['order', 'product'])->latest()->limit(10)->get();
        $myAddresses = Auth::check()
            ? UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get()
            : collect();
        $recentPayments = Payment::with(['order'])->latest()->limit(10)->get();

        $data = [
            'message' => 'Staff Dashboard',
            'deliveryByStatus' => $deliveryByStatus,
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

    // Staff Orders (view and process)
    Route::get('/orders', [\App\Http\Controllers\Admin\OrdersController::class, 'index'])->name('staff.orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrdersController::class, 'show'])->name('staff.orders.show');
    Route::put('/orders/{order}/delivery-status', [\App\Http\Controllers\Admin\OrdersController::class, 'updateDeliveryStatus'])->name('staff.orders.delivery.update');

    // My Activity Logs (staff sees own)
    Route::get('/activity-logs', [\App\Http\Controllers\Staff\ActivityLogController::class, 'index'])->name('staff.activity-logs.index');
});

// Driver routes
Route::middleware(['auth', 'role:driver'])->prefix('driver')->group(function () {
    Route::get('/dashboard', function () {
        /** @var User|null $user */
        $user = Auth::user();

        $deliveryByStatus = Order::selectRaw('delivery_status, COUNT(*) as count')->groupBy('delivery_status')->pluck('count', 'delivery_status')->toArray();
        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status')->toArray();
        $recentOrders = Order::with(['user'])->latest()->limit(10)->get();
        $recentOrderItems = OrderItem::with(['order', 'product'])->latest()->limit(10)->get();
        $myAddresses = Auth::check()
            ? UserAddress::where('user_id', Auth::id())->latest()->limit(10)->get()
            : collect();
        $recentPayments = Payment::with(['order'])->latest()->limit(10)->get();

        $data = [
            'message' => 'Driver Dashboard',
            'deliveryByStatus' => $deliveryByStatus,
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

    // Driver Orders
    Route::get('/orders', [App\Http\Controllers\Driver\OrdersController::class, 'index'])->name('driver.orders.index');

    // Orders Map page (renders the Livewire map component)
    Route::get('/orders/map', function () {
        return view('driver.orders.index');
    })->name('driver.orders.map');

    Route::get('/orders/{order}', [App\Http\Controllers\Driver\OrdersController::class, 'show'])->name('driver.orders.show');
    Route::put('/orders/{order}/delivery-status', [App\Http\Controllers\Driver\OrdersController::class, 'updateDeliveryStatus'])->name('driver.orders.delivery.update');
});

// Test route for ProductRepositoryInterface
Route::get('/test-product-repository', TestController::class);

// Alternative Map route (auth only) to bypass role restriction during debugging
Route::middleware(['auth'])->get('/map/orders', function () {
    return view('driver.orders.index');
})->name('orders.map.alt');

require __DIR__ . '/auth.php';

// Client routes
Route::middleware(['auth', 'verified', 'role:client'])->prefix('client')->group(function () {
    Route::get('/orders', function () {
        $orders = \App\Models\Order::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->latest()
            ->paginate(15);
        $title = __('My Orders');
        return view('client.orders.index', compact('orders', 'title'));
    })->name('client.orders.index');

    Route::get('/orders/{order}', function (\App\Models\Order $order) {
        abort_unless($order->user_id === \Illuminate\Support\Facades\Auth::id(), 403);
        return view('client.orders.show', compact('order'));
    })->name('client.orders.show');
});
