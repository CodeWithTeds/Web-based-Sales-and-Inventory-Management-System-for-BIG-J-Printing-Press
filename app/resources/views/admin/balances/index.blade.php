<x-layouts.app :title="__('Balances')">
    <div class="p-4">
        <flux:heading size="xl">{{ __('Outstanding Balances') }}</flux:heading>

        <form method="GET" class="mt-4 flex gap-2">
            <flux:input name="search" value="{{ request('search') }}" placeholder="Search orders or customers" />
            <flux:button type="submit" icon="magnifying-glass">{{ __('Search') }}</flux:button>
        </form>

        @if(session('status'))
            <div class="mt-4 rounded-md border border-green-200 bg-green-50 text-green-800 px-3 py-2">
                {{ session('status') }}
            </div>
        @endif
        @error('email')
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 text-red-700 px-3 py-2">
                {{ $message }}
            </div>
        @enderror

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="px-3 py-2">Order #</th>
                        <th class="px-3 py-2">Customer</th>
                        <th class="px-3 py-2">Email</th>
                        <th class="px-3 py-2">Total</th>
                        <th class="px-3 py-2">Downpayment</th>
                        <th class="px-3 py-2">Paid (sum)</th>
                        <th class="px-3 py-2">Remaining</th>
                        <th class="px-3 py-2">Latest Due Date</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $order->order_number }}</td>
                            <td class="px-3 py-2">{{ $order->customer_name ?? optional($order->user)->name }}</td>
                            <td class="px-3 py-2">{{ $order->customer_email ?? optional($order->user)->email }}</td>
                            <td class="px-3 py-2">₱{{ number_format($order->total, 2) }}</td>
                            <td class="px-3 py-2">₱{{ number_format($order->downpayment ?? 0, 2) }}</td>
                            <td class="px-3 py-2">₱{{ number_format($order->computed_paid_sum, 2) }}</td>
                            <td class="px-3 py-2 font-semibold">₱{{ number_format($order->computed_remaining, 2) }}</td>
                            <td class="px-3 py-2">{{ optional($order->computed_latest_due)->format('Y-m-d') }}</td>
                            <td class="px-3 py-2">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.balances.payments.create', $order) }}" class="text-blue-600">Add Payment</a>
                                    <form method="POST" action="{{ route('admin.balances.reminder', $order) }}">
                                        @csrf
                                        <flux:button type="submit" size="xs" icon="envelope">Send Reminder</flux:button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-4 text-center text-zinc-500">No orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</x-layouts.app>