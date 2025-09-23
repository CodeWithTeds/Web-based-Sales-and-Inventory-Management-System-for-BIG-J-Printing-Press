<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Details') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Order No.</div>
                        <div class="text-lg font-mono">{{ $order->order_number }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="flex items-center justify-end space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">{{ $order->status }}</span>
                            <form method="POST" action="{{ route('admin.orders.delivery.update', $order) }}">
                                @csrf
                                @method('PUT')
                                <div class="inline-flex items-center space-x-2">
                                    <select name="delivery_status" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($deliveryStatuses as $status)
                                            <option value="{{ $status }}" @selected($order->delivery_status === $status)>{{ \Illuminate\Support\Str::of($status)->replace('_',' ')->title() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 bg-green-50 text-green-700 rounded">{{ session('status') }}</div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Customer</h3>
                        <div class="text-gray-800">{{ $order->customer_name ?? $order->user->name ?? '—' }}</div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Payment</h3>
                        <div class="text-sm text-gray-600">Reference: {{ optional($order->payments->last())->reference ?? '—' }}</div>
                        <div class="text-sm text-gray-600">Total: ₱{{ number_format($order->total, 2) }}</div>
                    </div>
                </div>

                @php
                    $lastPaymentMethod = optional($order->payments->last())->method;
                    $isGcash = $lastPaymentMethod && strcasecmp($lastPaymentMethod, 'GCASH') === 0;
                @endphp

                @if($address && $isGcash)
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Address</h3>
                    <div class="space-y-1 text-sm">
                        <div><span class="text-gray-500">Exact:</span> <span class="text-gray-800">{{ $address->exact_address }}</span></div>
                        <div><span class="text-gray-500">Barangay:</span> <span class="text-gray-800">{{ $addressNames['barangay_name'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500">City / Municipality:</span> <span class="text-gray-800">{{ $addressNames['city_name'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500">Province:</span> <span class="text-gray-800">{{ $addressNames['province_name'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500">Region:</span> <span class="text-gray-800">{{ $addressNames['region_name'] ?? '—' }}</span></div>
                    </div>
                </div>
                @endif
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-sm">{{ $item->name }}</td>
                                        <td class="px-3 py-2 text-sm">{{ $item->qty }}</td>
                                        <td class="px-3 py-2 text-sm">₱{{ number_format($item->price, 2) }}</td>
                                        <td class="px-3 py-2 text-sm">₱{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Back to Orders</a>
                    <a href="{{ route('admin.pos.receipt', $order) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">View Receipt</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>