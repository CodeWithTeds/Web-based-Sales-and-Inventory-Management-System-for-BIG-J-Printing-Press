<x-layouts.app :title="__('Edit Payment')">
    <div class="p-4 max-w-xl">
        <flux:heading size="xl">{{ __('Edit Payment') }}</flux:heading>
        <p class="text-sm text-zinc-600">Order #{{ $order->order_number }} • Customer: {{ $order->customer_name ?? optional($order->user)->name }}</p>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('admin.balances.payments.update', [$order, $payment]) }}">
            @csrf
            @method('PUT')
            <div>
                <flux:input name="amount" type="number" step="0.01" min="0" label="Amount" required value="{{ old('amount', $payment->amount) }}" />
                @error('amount')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div>
                <flux:input name="due_date" type="date" label="Due date (optional)" value="{{ old('due_date', optional($payment->due_date)->format('Y-m-d')) }}" />
                @error('due_date')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>
            <div>
                <flux:input name="notes" label="Notes (optional)" value="{{ old('notes', $payment->notes) }}" />
                @error('notes')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
            </div>

            <div class="flex gap-2">
                <flux:button type="submit" icon="check">{{ __('Update Payment') }}</flux:button>
                <flux:button href="{{ route('admin.balances.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>