@php
$pageTitle = 'Quick Purchase Request';
@endphp

<x-layouts.app :title="$pageTitle">
    <div class="max-w-6xl mx-auto space-y-8 py-8">

        <!-- Header (smaller text, no background) -->
        <div class="relative overflow-hidden rounded-3xl shadow-sm ring-1 ring-black/5">
            <div class="p-6 md:p-8">
                <p class="mt-2 text-xs md:text-sm text-zinc-700">Create a clean, fast purchase request with guided steps. Choose a category, product, and options — then submit for review.</p>
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
            <!-- Pending PR notice: block new PR creation until approval -->
            @if (!empty($hasPending) && $hasPending && !empty($pendingOrder))
                <div class="rounded-xl border border-yellow-300 bg-yellow-50 p-5 mb-6">
                    <div class="flex items-start gap-3">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mt-0.5 text-yellow-600"><path d="M12 9v4m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">You have a pending Purchase Request.</p>
                            <p class="text-sm text-yellow-700">PR <span class="font-semibold">#{{ $pendingOrder->order_number }}</span> is waiting for admin approval. You cannot create another request until it’s approved.</p>
                            <div class="mt-3">
                                <a href="{{ route('client.orders.show', $pendingOrder) }}" class="inline-flex items-center rounded-lg border border-yellow-300 bg-white px-3 py-1.5 text-xs font-medium text-yellow-800 hover:bg-yellow-100">View pending PR</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Dynamic PR Form: Category → Product → Size → Paper Type → Ply -->
            @if (empty($hasPending) || !$hasPending)
                <div class="mt-2 rounded-xl border border-zinc-100 bg-white p-8 shadow-sm">
                    <h2 class="text-sm md:text-base font-semibold mb-6 text-zinc-900">Select Options</h2>
                    <form method="POST" action="{{ route('client.purchase-requests.store') }}" id="dynamicPrForm" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- 1. Product Category -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Product Category<span class="text-red-500"> *</span></label>
                                <select id="prCategory" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition disabled:opacity-50" required>
                                    <option value="">Select a category...</option>
                                    @php
                                        $cats = isset($productCategories) ? $productCategories : collect([]);
                                    @endphp
                                    @foreach ($cats as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. Product Name -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Product Name<span class="text-red-500"> *</span></label>
                                <select id="prProduct" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition disabled:opacity-50" required disabled>
                                    <option value="">Select a product...</option>
                                </select>
                            </div>

                            <!-- 3. Size -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Size<span class="text-red-500"> *</span></label>
                                <select id="prSize" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition disabled:opacity-50" required disabled>
                                    <option value="">Select a size...</option>
                                </select>
                            </div>

                            <!-- 4. Paper Type -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Paper Type<span class="text-red-500"> *</span></label>
                                <select id="prPaperType" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition">
                                    <option value="">Select a paper type...</option>
                                </select>
                            </div>

                            <!-- Quantity (required for PR items) -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Quantity<span class="text-red-500"> *</span></label>
                                <input type="number" id="prQty" min="1" value="1" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition" required />
                            </div>

                            <!-- Unit (auto from selected product) -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Unit</label>
                                <input type="text" id="prUnit" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition" placeholder="Auto-filled from product" readonly />
                            </div>
                        </div>

                        <!-- Add item action -->
                        <div class="flex items-center justify-end">
                            <button type="button" id="prAddItem" class="inline-flex items-center rounded-xl bg-[var(--color-primary)]/90 px-4 py-2 text-sm font-medium text-white shadow-sm hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition">
                                <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Add Item
                            </button>
                        </div>

                        <!-- Items list -->
                        <div class="mt-6">
                            <h3 class="text-xs md:text-sm font-semibold text-zinc-900 mb-3">Items in Request</h3>
                            <div id="prItemsList" class="rounded-xl border border-zinc-200 bg-white overflow-hidden">
                                <div class="p-4 text-sm text-zinc-500">No items added yet. Select options above and click Add Item.</div>
                            </div>
                        </div>

                        <!-- 5. Ply (receipts only) -->
                        <div id="plySection" class="grid grid-cols-1 md:grid-cols-2 gap-8 hidden">
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Ply<span class="text-red-500"> *</span></label>
                                <select id="prPly" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition">
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
                            <textarea name="purpose" id="prPurpose" rows="4" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition" placeholder="Describe why these items are needed" required>{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <input type="hidden" name="items" id="prItemsPayload" />

                        <div class="flex items-center justify-end gap-4">
                            <button type="button" id="prReset" class="inline-flex items-center rounded-xl bg-white px-4 py-2 text-sm font-medium text-zinc-700 border border-zinc-300 hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-brand)] transition">
                                <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Reset
                            </button>
                            <button type="submit" class="inline-flex items-center rounded-xl bg-gradient-to-r from-[var(--color-primary)] via-[var(--color-accent-brand)] to-[var(--color-primary)] px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition">
                                <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12l5 5L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Save & Send for Review
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="mt-2 rounded-xl border border-zinc-100 bg-white p-8 shadow-sm">
                    <p class="text-sm text-zinc-700">A pending Purchase Request is awaiting approval. Please wait until it’s approved before creating a new one.</p>
                </div>
            @endif

            <script src="{{ asset('js/quick-pr.js') }}"></script>
        </div>

        @php
            $flashMessage = session('status') ?: session('error');
        @endphp
        @if (!empty($flashMessage))
            <!-- Hook for JS module to show SweetAlert (Waiting for Approval) -->
            <div id="prStatus" data-message="{{ $flashMessage }}" class="hidden"></div>
        @endif
    </div>
</x-layouts.app>