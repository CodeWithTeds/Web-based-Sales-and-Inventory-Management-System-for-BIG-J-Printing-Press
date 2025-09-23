<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __($title ?? 'Orders') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex items-center space-x-3">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..." class="w-64 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Search</button>
                        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Reset</a>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No.</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                            
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                            @foreach($orders as $order)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700">{{ $order->id }}</td>
                                    <td class="px-3 py-2 text-sm font-mono">{{ $order->order_number }}</td>
                                    <td class="px-3 py-2 text-sm">{{ $order->customer_name ?? $order->user->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm">₱{{ number_format($order->total, 2) }}</td>
                                    <td class="px-3 py-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">{{ $order->status }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        @php
                                            $lastPaymentMethod = optional($order->payments->last())->method;
                                            $isGcash = $lastPaymentMethod && strcasecmp($lastPaymentMethod, 'GCASH') === 0;
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-800">{{ $isGcash ? ($order->delivery_status ?? '—') : 'N/A' }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-600">
                                        {{ $order->items->sum('qty') }} items
                                    </td>
                                    <td class="px-3 py-2 text-xs text-gray-600">
                                        {{ ($isGcash ?? false) ? 'GCASH' : 'CASH' }}
                                    </td>
                                   
                                    <td class="px-3 py-2 text-xs text-gray-600">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>