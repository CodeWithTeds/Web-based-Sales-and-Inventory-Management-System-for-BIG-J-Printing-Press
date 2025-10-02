<x-layouts.app :title="__('Add Payment')">
    <div class="p-4">
        <flux:heading size="xl">{{ __('Add Payment for Order #:number', ['number' => $order->order_number]) }}</flux:heading>

        <form class="mt-6 space-y-4" method="POST" action="{{ route('admin.outstanding-balances.payments.store', $order) }}">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label for="amount">{{ __('Amount') }}</flux:label>
                    <flux:input id="amount" name="amount" type="number" step="0.01" min="0.01" required />
                </div>
                <div>
                    <flux:label for="payment_method">{{ __('Payment Method') }}</flux:label>
                    <flux:select id="method" name="method" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank">Bank Transfer</option>
                    </flux:select>
                </div>
                <div>
                    <flux:label for="reference_number">{{ __('Reference Number') }}</flux:label>
                    <flux:input id="reference" name="reference" />
                </div>
                <div>
                    <flux:label for="due_date">{{ __('Due Date') }}</flux:label>
                    <flux:input id="due_date" name="due_date" type="date" />
                </div>
                <div class="sm:col-span-2">
                    <flux:label for="notes">{{ __('Notes') }}</flux:label>
                    <flux:textarea id="notes" name="notes" rows="3"></flux:textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button href="{{ route('admin.outstanding-balances.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit">{{ __('Save Payment') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>