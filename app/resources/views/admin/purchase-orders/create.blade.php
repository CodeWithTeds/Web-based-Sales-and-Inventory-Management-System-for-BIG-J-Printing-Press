@php
    $pageTitle = 'Quick Purchase Order';
@endphp

<x-layouts.app :title="$pageTitle">
    <div class="max-w-6xl mx-auto space-y-8 py-8">

        <!-- Header (match client PR style) -->
        <div class="relative overflow-hidden rounded-3xl shadow-sm ring-1 ring-black/5">
            <div class="p-6 md:p-8">
                <p class="mt-2 text-xs md:text-sm text-zinc-700">Create a clean, fast purchase order with guided steps. Choose a category, product, and options — then submit.</p>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium text-zinc-600">Category</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium text-zinc-600">Product</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium text-zinc-600">Size</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium text-zinc-600">Paper</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium text-zinc-600">Qty</span>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 p-8 shadow-lg ring-1 ring-black/5">
            <div class="mt-2 rounded-xl border border-zinc-100 bg-white p-8 shadow-sm">
                <h2 class="text-sm md:text-base font-semibold mb-6 text-zinc-900">Select Options</h2>
                <form method="POST" action="{{ route('admin.purchase-orders.store') }}" id="poForm" class="space-y-6">
                    @csrf

                    <!-- Customer (one customer, multiple items/orders) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Customer Name</label>
                            <input type="text" name="customer_name" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" value="{{ old('customer_name') }}" />
                        </div>
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Customer Email</label>
                            <input type="email" name="customer_email" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" value="{{ old('customer_email') }}" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- 1. Product Category -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Product Category<span class="text-red-500"> *</span></label>
                            <select id="poCategory" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" required>
                                <option value="">Select a category...</option>
                            </select>
                        </div>

                        <!-- 2. Product Name -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Product Name<span class="text-red-500"> *</span></label>
                            <select id="poProduct" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" required disabled>
                                <option value="">Select a product...</option>
                            </select>
                        </div>

                        <!-- 3. Size -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Size<span class="text-red-500"> *</span></label>
                            <select id="poSize" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" required disabled>
                                <option value="">Select a size...</option>
                            </select>
                        </div>

                        <!-- 4. Paper Type -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Paper Type<span class="text-red-500"> *</span></label>
                            <select id="poPaperType" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2">
                                <option value="">Select a paper type...</option>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Quantity<span class="text-red-500"> *</span></label>
                            <input type="number" id="poQty" min="1" value="1" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" required />
                        </div>

                        <!-- Unit (auto) -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Unit</label>
                            <input type="text" id="poUnit" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" placeholder="Auto-filled from product" readonly />
                        </div>
                        <!-- Price (auto) -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Price</label>
                            <input type="text" id="poPrice" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" placeholder="Auto-filled from product" readonly />
                        </div>
                    </div>

                    <!-- Add item action -->
                    <div class="flex items-center justify-end">
                        <button type="button" id="poAddItem" class="inline-flex items-center rounded-xl bg-[var(--color-primary)]/90 px-4 py-2 text-sm font-medium text-white shadow-sm hover:brightness-95">
                            <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Add Item
                        </button>
                    </div>

                    <!-- Items list -->
                    <div class="mt-6">
                        <h3 class="text-xs md:text-sm font-semibold text-zinc-900 mb-3">Items in Order</h3>
                        <div id="poItemsList" class="rounded-xl border border-zinc-200 bg-white overflow-hidden">
                            <div class="p-4 text-sm text-zinc-500">No items added yet. Select options above and click Add Item.</div>
                        </div>
                    </div>

                    <!-- 5. Ply (receipts only) -->
                    <div id="plySection" class="grid grid-cols-1 md:grid-cols-2 gap-8 hidden">
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Ply<span class="text-red-500"> *</span></label>
                            <select id="poPly" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2">
                                <option value="">Select ply...</option>
                                <option value="2">Duplicate (2-ply)</option>
                                <option value="3">Triplicate (3-ply)</option>
                                <option value="4">Quadruplicate (4-ply)</option>
                                <option value="5">Quintuplicate (5-ply)</option>
                            </select>
                        </div>
                        <div id="plyColors" class="hidden"></div>
                    </div>

                    <!-- Purpose / Note -->
                    <div>
                        <label class="block text-xs md:text-sm font-medium text-zinc-700">Purpose / Note<span class="text-red-500"> *</span></label>
                        <textarea name="purpose" id="poPurpose" rows="4" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2" placeholder="Describe why these items are needed" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <input type="hidden" name="itemsPayload" id="poItemsPayload" />

                    <div class="flex items-center justify-end gap-4">
                        <button type="button" id="poReset" class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-medium text-zinc-700 border border-zinc-300 hover:brightness-95">
                            <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Reset
                        </button>
                        <button type="submit" class="inline-flex items-center rounded-xl bg-gradient-to-r from-[var(--color-primary)] via-[var(--color-accent-brand)] to-[var(--color-primary)] px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md">
                            <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12l5 5L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Create Purchase Order
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @php
            $flashMessage = session('status') ?: session('error');
        @endphp
        @if (!empty($flashMessage))
            <div id="poStatus" data-message="{{ $flashMessage }}" class="hidden"></div>
        @endif
    </div>

    <script src="{{ asset('js/quick-po.js') }}" defer></script>
</x-layouts.app>