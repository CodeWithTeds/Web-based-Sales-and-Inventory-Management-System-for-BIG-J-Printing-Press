<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <!-- Enhanced Welcome Section -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#D62F1A] to-[#BB822B] p-6 text-white shadow-lg">
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        @if(auth()->check() && auth()->user()->isDriver())
                        <p class="text-sm text-white/90 opacity-90">{{ __('Welcome, Driver!') }}</p>
                        @else
                        <p class="text-sm text-white/90 opacity-90">{{ __('Welcome back') }}</p>
                        @endif
                        <h2 class="text-2xl font-bold text-white">{{ auth()->user()->name }}</h2>
                        @if(auth()->check() && auth()->user()->isDriver())
                        <p class="mt-2 text-white/90 opacity-90">{{ __('Here are your latest assignments and delivery updates.') }}</p>
                        @else
                        <p class="mt-2 text-white/90 opacity-90">{{ __('Here is an overview of your inventory and orders.') }}</p>
                        @endif
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                        <x-app-logo-icon class="size-7 text-white" />
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                        <p class="text-xs text-white/90 opacity-90">{{ __('Total Orders') }}</p>
                        <p class="text-lg font-semibold text-white">{{ number_format($totalOrders ?? 0) }}</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                        <p class="text-xs text-white/90 opacity-90">{{ __('Items Sold') }}</p>
                        <p class="text-lg font-semibold text-white">{{ number_format($itemsSold ?? 0) }}</p>
                    </div>
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                        <p class="text-xs text-white/90 opacity-90">{{ __('Active Users') }}</p>
                        <p class="text-lg font-semibold text-white">{{ App\Models\User::count() }}</p>
                    </div>
                    <div class="rounded-xl bg-white/10 p-3 backdrop-blur-sm">
                        <p class="text-xs text-white/90 opacity-90">{{ __('Revenue') }}</p>
                        <p class="text-lg font-semibold text-white">₱{{ number_format(App\Models\Order::sum('total') ?? 0, 2) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Background pattern -->
            <div class="absolute inset-0 z-0 opacity-10">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D62F1A]/20 to-[#BB822B]/20"></div>
            </div>
        </div>

        @if(auth()->check() && auth()->user()->isDriver())
            {{-- Removed Orders Map from dashboard --}}
            {{-- @livewire('driver.orders-map') --}}
        @endif

        {{-- Only show admin/staff widgets for non-client users --}}
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
        @if(auth()->user()->isAdmin())
         <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
             <!-- Delivery by Status -->
             <div class="rounded-2xl bg-white p-6 shadow-lg border border-gray-100">
                 <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Delivery by Status') }}</h3>
                 <div class="space-y-3">
                     @foreach($deliveryByStatus as $status => $count)
                     <div class="flex items-center justify-between">
                         <span class="text-sm text-gray-600">{{ __($status) }}</span>
                         <span class="text-sm font-medium text-[#D62F1A]">{{ $count }}</span>
                     </div>
                     @endforeach
                 </div>
             </div>

             <!-- Orders by Status -->
             <div class="rounded-2xl bg-white p-6 shadow-lg border border-gray-100">
                 <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Orders by Status') }}</h3>
                 <div class="space-y-3">
                     @foreach($ordersByStatus as $status => $count)
                     <div class="flex items-center justify-between">
                         <span class="text-sm text-gray-600">{{ __($status) }}</span>
                         <span class="text-sm font-medium text-[#D62F1A]">{{ $count }}</span>
                     </div>
                     @endforeach
                 </div>
             </div>


         </div>
        @endif

         <!-- Business Overview -->
         <div class="rounded-2xl bg-white p-6 shadow-lg border border-gray-100">
             <h3 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Business Overview') }}</h3>
             <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                 <div class="text-center">
                     <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F8F8F5] border border-gray-200">
                         <svg class="h-6 w-6 text-[#D62F1A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                         </svg>
                     </div>
                     <p class="text-sm text-gray-600">{{ __('Total Orders') }}</p>
                     <p class="text-lg font-semibold text-[#D62F1A]">{{ App\Models\Order::count() }}</p>
                 </div>
                 <div class="text-center">
                     <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F8F8F5] border border-gray-200">
                         <svg class="h-6 w-6 text-[#D62F1A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"></path>
                         </svg>
                     </div>
                     <p class="text-sm text-gray-600">{{ __('Items Sold') }}</p>
                     <p class="text-lg font-semibold text-[#D62F1A]">{{ App\Models\OrderItem::sum('qty') }}</p>
                 </div>
                 <div class="text-center">
                     <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F8F8F5] border border-gray-200">
                         <svg class="h-6 w-6 text-[#D62F1A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                         </svg>
                     </div>
                     <p class="text-sm text-gray-600">{{ __('Active Users') }}</p>
                     <p class="text-lg font-semibold text-[#D62F1A]">{{ App\Models\User::count() }}</p>
                 </div>
                 <div class="text-center">
                     <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-[#F8F8F5] border border-gray-200">
                         <svg class="h-6 w-6 text-[#D62F1A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                         </svg>
                     </div>
                     <p class="text-sm text-gray-600">{{ __('Revenue') }}</p>
                     <p class="text-lg font-semibold text-[#D62F1A]">₱{{ number_format(App\Models\Order::sum('total'), 2) }}</p>
                 </div>
             </div>
         </div>
         @endif

        <!-- Bottom: Order Tools -->
        {{-- Admin/staff-only order tools --}}
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Orders') }}</h3>
            <div class="flex flex-wrap gap-2">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.orders.index') : route('staff.orders.index') }}" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                    {{ __('Track Order Status') }}
                </a>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.orders.index') : route('staff.orders.index') }}" class="inline-flex items-center gap-2 rounded-md bg-amber-600 px-3 py-2 text-xs font-medium text-white hover:bg-amber-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3"/></svg>
                    {{ __('View Order History') }}
                </a>
            </div>
        </div>
        @endif

        {{-- Real Data Tables --}}
        @if(auth()->check() && auth()->user()->isAdmin())
         <div class="grid gap-4 md:grid-cols-2">
             <!-- Orders Table -->
             <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                 <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Recent Orders') }}</h3>
                 <div class="overflow-x-auto">
                     <table class="min-w-full text-xs">
                         <thead>
                             <tr class="text-left text-gray-500 dark:text-neutral-400">
                                 <th class="px-2 py-1">#</th>
                                 <th class="px-2 py-1">{{ __('Customer') }}</th>
                                 <th class="px-2 py-1">{{ __('Total') }}</th>
                                 <th class="px-2 py-1">{{ __('Status') }}</th>
                                 <th class="px-2 py-1">{{ __('Delivery') }}</th>
                                 <th class="px-2 py-1">{{ __('Created') }}</th>
                             </tr>
                         </thead>
                         <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                             @forelse(($recentOrders ?? []) as $order)
                                 <tr class="text-gray-800 dark:text-neutral-200">
                                     <td class="px-2 py-1 font-mono">{{ $order->order_number }}</td>
                                     <td class="px-2 py-1">{{ $order->user->name ?? $order->customer_name ?? '—' }}</td>
                                     <td class="px-2 py-1">₱{{ number_format($order->total ?? 0, 2) }}</td>
                                     <td class="px-2 py-1">{{ $order->status ?? '—' }}</td>
                                     <td class="px-2 py-1">{{ $order->delivery_status ?? '—' }}</td>
                                     <td class="px-2 py-1">{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="6" class="px-2 py-2 text-center text-gray-500">{{ __('No orders yet') }}</td>
                                 </tr>
                             @endforelse
                         </tbody>
                     </table>
                 </div>
             </div>

             <!-- Order Items Table -->
             <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                 <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Recent Order Items') }}</h3>
                 <div class="overflow-x-auto">
                     <table class="min-w-full text-xs">
                         <thead>
                             <tr class="text-left text-gray-500 dark:text-neutral-400">
                                 <th class="px-2 py-1">#</th>
                                 <th class="px-2 py-1">{{ __('Item') }}</th>
                                 <th class="px-2 py-1">{{ __('Qty') }}</th>
                                 <th class="px-2 py-1">{{ __('Price') }}</th>
                                 <th class="px-2 py-1">{{ __('Line Total') }}</th>
                             </tr>
                         </thead>
                         <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                             @forelse(($recentOrderItems ?? []) as $item)
                                 <tr class="text-gray-800 dark:text-neutral-200">
                                     <td class="px-2 py-1 font-mono">{{ $item->order->order_number ?? $item->order_id }}</td>
                                     <td class="px-2 py-1">{{ $item->name ?? $item->product->name ?? '—' }}</td>
                                     <td class="px-2 py-1">{{ $item->qty }}</td>
                                     <td class="px-2 py-1">₱{{ number_format($item->price ?? 0, 2) }}</td>
                                     <td class="px-2 py-1">₱{{ number_format($item->line_total ?? ($item->qty * ($item->price ?? 0)), 2) }}</td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="5" class="px-2 py-2 text-center text-gray-500">{{ __('No items found') }}</td>
                                 </tr>
                             @endforelse
                         </tbody>
                     </table>
                 </div>
             </div>
         </div>
         @endif

        <!-- My Addresses Table - Visible to all users -->
         <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
             <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('My Addresses') }}</h3>
             <div class="overflow-x-auto">
                 <table class="min-w-full text-xs">
                     <thead>
                         <tr class="text-left text-gray-500 dark:text-neutral-400">
                             @if(auth()->check() && auth()->user()->isAdmin())
                                 <th class="px-2 py-1">{{ __('User') }}</th>
                             @endif
                             <th class="px-2 py-1">{{ __('Region') }}</th>
                             <th class="px-2 py-1">{{ __('Province') }}</th>
                             <th class="px-2 py-1">{{ __('City') }}</th>
                             <th class="px-2 py-1">{{ __('Barangay') }}</th>
                             <th class="px-2 py-1">{{ __('Exact Address') }}</th>
                             <th class="px-2 py-1">{{ __('Default') }}</th>
                         </tr>
                     </thead>
                     <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                         @forelse(($myAddresses ?? []) as $addr)
                             <tr class="text-gray-800 dark:text-neutral-200">
                                 @if(auth()->check() && auth()->user()->isAdmin())
                                     <td class="px-2 py-1">{{ $addr->user->name ?? 'N/A' }}</td>
                                 @endif
                                 <td class="px-2 py-1">{{ $addr->region_code ?? '—' }}</td>
                                 <td class="px-2 py-1">{{ $addr->province_code ?? '—' }}</td>
                                 <td class="px-2 py-1">{{ $addr->city_code ?? '—' }}</td>
                                 <td class="px-2 py-1">{{ $addr->barangay_code ?? '—' }}</td>
                                 <td class="px-2 py-1">{{ $addr->exact_address ?? '—' }}</td>
                                 <td class="px-2 py-1">
                                     @if($addr->is_default)
                                         <span class="badge badge-success">{{ __('Yes') }}</span>
                                     @else
                                         <span class="badge badge-secondary">{{ __('No') }}</span>
                                     @endif
                                 </td>
                             </tr>
                         @empty
                             <tr>
                                 <td colspan="{{ auth()->check() && auth()->user()->isAdmin() ? 7 : 6 }}" class="px-2 py-2 text-center text-gray-500">{{ __('You have no saved addresses') }}</td>
                             </tr>
                         @endforelse
                     </tbody>
                 </table>
             </div>
         </div>

        @if(auth()->check() && auth()->user()->isAdmin())
        <div class="grid gap-4 md:grid-cols-2">

            <!-- Payments Table -->
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Recent Payments') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-neutral-400">
                                <th class="px-2 py-1">#</th>
                                <th class="px-2 py-1">{{ __('Provider') }}</th>
                                <th class="px-2 py-1">{{ __('Method') }}</th>
                                <th class="px-2 py-1">{{ __('Amount') }}</th>
                                <th class="px-2 py-1">{{ __('Currency') }}</th>
                                <th class="px-2 py-1">{{ __('Reference') }}</th>
                                <th class="px-2 py-1">{{ __('Paid At') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @forelse(($recentPayments ?? []) as $pay)
                                <tr class="text-gray-800 dark:text-neutral-200">
                                    <td class="px-2 py-1 font-mono">{{ $pay->order->order_number ?? $pay->order_id }}</td>
                                    <td class="px-2 py-1">{{ $pay->provider ?? '—' }}</td>
                                    <td class="px-2 py-1">{{ $pay->method ?? '—' }}</td>
                                    <td class="px-2 py-1">₱{{ number_format($pay->amount ?? 0, 2) }}</td>
                                    <td class="px-2 py-1">{{ $pay->currency ?? '—' }}</td>
                                    <td class="px-2 py-1">{{ $pay->reference ?? '—' }}</td>
                                    <td class="px-2 py-1">{{ optional($pay->paid_at)->format('Y-m-d H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-2 py-2 text-center text-gray-500">{{ __('No payments found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Chart.js CDN and initialization -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            (function() {
                var canvas = document.getElementById('ordersStatusChart');
                if (!canvas) return;
                var labels = [];
                var data = [];
                try {
                    labels = canvas.dataset.labels ? JSON.parse(canvas.dataset.labels) : [];
                    data = canvas.dataset.data ? JSON.parse(canvas.dataset.data) : [];
                } catch (e) {
                    labels = [];
                    data = [];
                }
                var ctx = canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels.length ? labels : ['No Data'],
                        datasets: [{
                            data: data.length ? data : [1],
                            backgroundColor: ['#f87171','#60a5fa','#fbbf24','#34d399','#a78bfa','#fdba74'],
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            })();
        </script>
        @endif
    </div>
</x-layouts.app>