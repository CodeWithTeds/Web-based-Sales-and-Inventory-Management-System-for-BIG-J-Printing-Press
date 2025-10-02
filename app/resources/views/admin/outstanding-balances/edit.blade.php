<x-layouts.app :title="__('Edit Payment')">
    <div class="p-4">
        <flux:heading size="xl">{{ __('Edit Payment for Order #:number', ['number' => $order->order_number]) }}</flux:heading>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('admin.outstanding-balances.payments.update', $payment) }}">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label for="amount">{{ __('Amount') }}</flux:label>
                    <flux:input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ $payment->amount }}" required />
                </div>
                <div>
                    <flux:label for="payment_method">{{ __('Payment Method') }}</flux:label>
                    <flux:select id="method" name="method" required>
                        <option value="cash" @selected($payment->method==='cash')>Cash</option>
                        <option value="card" @selected($payment->method==='card')>Card</option>
                        <option value="bank" @selected($payment->method==='bank')>Bank Transfer</option>
                    </flux:select>
                </div>
                <div>
                    <flux:label for="reference_number">{{ __('Reference Number') }}</flux:label>
                    <flux:input id="reference" name="reference" value="{{ $payment->reference }}" />
                </div>
                <div>
                    <flux:label for="due_date">{{ __('Due Date') }}</flux:label>
                    <flux:input id="due_date" name="due_date" type="date" value="{{ $payment->due_date ? $payment->due_date->format('Y-m-d') : '' }}" />
                </div>
                <div class="sm:col-span-2">
                    <flux:label for="notes">{{ __('Notes') }}</flux:label>
                    <flux:textarea id="notes" name="notes" rows="3">{{ $payment->notes }}</flux:textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button href="{{ route('admin.outstanding-balances.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit">{{ __('Update Payment') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>