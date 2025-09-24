<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Welcome Section -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">{{ __('Welcome back') }}</p>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name ?? __('User') }}</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-neutral-300">{{ __('Here is an overview of your inventory and orders.') }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                    <x-app-logo-icon class="size-6" />
                </span>
            </div>
        </div>

        <!-- Top Grid: Pie Chart + Quick Actions + Placeholder -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Pie Chart Card -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Orders by Status') }}</h3>
                <canvas id="ordersStatusChart" class="w-full h-[240px]" data-labels='@json(array_keys($ordersByStatus ?? []))' data-data='@json(array_values($ordersByStatus ?? []))'></canvas>
            </div>

            <!-- Quick Actions -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Quick Actions') }}</h3>
                <div class="flex flex-col gap-2">
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
                        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                            {{ __('Track Order Status') }}
                        </a>
                        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-md bg-amber-600 px-3 py-2 text-xs font-medium text-white hover:bg-amber-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3"/></svg>
                            {{ __('View Order History') }}
                        </a>
                    @else
                        <a href="{{ route('client.ordering') }}" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('Start an Order') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Placeholder/KPIs Card -->
            <div class="relative overflow-hidden rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Overview') }}</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('Total Orders') }}</p>
                        <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($totalOrders ?? 0) }}</p>
                    </div>
                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                        <p class="text-zinc-500 dark:text-zinc-400">{{ __('Items Sold') }}</p>
                        <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($itemsSold ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom: Order Tools -->
        <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
            <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('Orders') }}</h3>
            <div class="flex flex-wrap gap-2">
                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isStaff()))
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                        {{ __('Track Order Status') }}
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 rounded-md bg-amber-600 px-3 py-2 text-xs font-medium text-white hover:bg-amber-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4 1.343 4 3"/></svg>
                        {{ __('View Order History') }}
                    </a>
                @else
                    <a href="{{ route('client.ordering') }}" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Start an Order') }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Real Data Tables --}}
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

        <div class="grid gap-4 md:grid-cols-2">
            <!-- My Addresses Table -->
            <div class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-zinc-900">
                <h3 class="mb-3 text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ __('My Addresses') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-neutral-400">
                                <th class="px-2 py-1">{{ __('Region') }}</th>
                                <th class="px-2 py-1">{{ __('Province') }}</th>
                                <th class="px-2 py-1">{{ __('City') }}</th>
                                <th class="px-2 py-1">{{ __('Barangay') }}</th>
                                <th class="px-2 py-1">{{ __('Exact Address') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                            @forelse(($myAddresses ?? []) as $addr)
                                <tr class="text-gray-800 dark:text-neutral-200">
                                    <td class="px-2 py-1">{{ $addr->region_code ?? '—' }}</td>
                                    <td class="px-2 py-1">{{ $addr->province_code ?? '—' }}</td>
                                    <td class="px-2 py-1">{{ $addr->city_code ?? '—' }}</td>
                                    <td class="px-2 py-1">{{ $addr->barangay_code ?? '—' }}</td>
                                    <td class="px-2 py-1">{{ $addr->exact_address ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-2 py-2 text-center text-gray-500">{{ __('You have no saved addresses') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

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
    </div>
</x-layouts.app>
