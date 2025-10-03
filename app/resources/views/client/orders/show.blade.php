<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Order Details') }}
            </h2>
            <a href="{{ route('client.orders.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-200 rounded-md text-xs font-medium text-gray-700 hover:bg-gray-300">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Summary</h3>
                        <div class="mt-3 space-y-1 text-sm text-gray-600">
                            <p>Order No.: <span class="font-mono text-indigo-600">{{ $order->order_number }}</span></p>
                            <p>Total: <span class="font-medium">₱{{ number_format($order->total, 2) }}</span></p>
                            <p>Status: <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $order->status }}</span></p>
                            <p>Delivery: <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $order->delivery_status ?? '—' }}</span></p>
                            <p>Placed: {{ $order->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Items</h3>
                        <ul class="mt-3 space-y-2">
                            @foreach($order->items as $item)
                                <li class="flex items-center justify-between text-sm">
                                    <span>{{ $item->product->name }} × {{ $item->qty }}</span>
                                    <span>₱{{ number_format($item->price * $item->qty, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                @if($order->payments->isNotEmpty())
                <div class="mt-6">
                    <h3 class="text-sm font-semibold text-gray-700">Payments</h3>
                    <div class="mt-3 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->payments as $payment)
                                    <tr>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">₱{{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">{{ strtoupper($payment->method ?? '—') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">{{ $payment->reference ?? '—' }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-sm">{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>