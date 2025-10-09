<x-layouts.app :title="__('Add Payment')">
    <div class="p-4">
        <flux:heading size="xl">{{ __('Add Payment for Order #:number', ['number' => $order->order_number]) }}</flux:heading>

        <x-auth-validation-errors class="mt-4 mb-4" :errors="$errors" />

        <form class="mt-6 space-y-4" method="POST" action="{{ route('admin.outstanding-balances.payments.store', $order) }}">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label for="amount">{{ __('Amount') }}</flux:label>
                    <flux:input id="amount" name="amount" type="number" step="0.01" min="0.01" required value="{{ old('amount') }}" />
                    @error('amount')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <flux:label for="payment_method">{{ __('Payment Method') }}</flux:label>
                    <flux:select id="method" name="method" required>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank">Bank Transfer</option>
                    </flux:select>
                    @error('method')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <flux:label for="reference_number">{{ __('Reference Number') }}</flux:label>
                    <flux:input id="reference" name="reference" value="{{ old('reference') }}" />
                    @error('reference')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <flux:label for="due_date">{{ __('Due Date') }}</flux:label>
                    <flux:input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}" />
                    @error('due_date')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="sm:col-span-2">
                    <flux:label for="notes">{{ __('Notes') }}</flux:label>
                    <flux:textarea id="notes" name="notes" rows="3">{{ old('notes') }}</flux:textarea>
                    @error('notes')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button href="{{ route('admin.outstanding-balances.index') }}" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit">{{ __('Save Payment') }}</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>