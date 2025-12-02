<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Request History') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-6 space-y-6 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Your Purchase Requests</h3>
                        <p class="text-xs text-gray-500">List of all PRs you created.</p>
                    </div>
                    <div>
                        <a href="{{ route('client.purchase-requests.select-category') }}" class="inline-flex items-center rounded-lg bg-[var(--color-primary)]/90 px-3 py-2 text-sm font-medium text-white shadow-sm hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition">New Purchase Request</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No.</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="px-3 py-2 text-sm font-mono">{{ $order->order_number }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">{{ $order->status }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-sm">₱{{ number_format((float) ($order->total ?? 0), 2) }}</td>
                                    <td class="px-3 py-2 text-sm">{{ is_string($order->delivery_date) ? $order->delivery_date : optional($order->delivery_date)->format('Y-m-d') }}</td>
                                    <td class="px-3 py-2 text-sm">{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <a href="{{ route('client.orders.show', $order) }}" class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No Purchase Requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>