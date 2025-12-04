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
                @if(isset($quotedOrder) && $quotedOrder)
                    <div class="rounded-xl border border-blue-300 bg-blue-50 p-5 mb-6">
                        <div class="flex items-start gap-3">
                            <!-- Info Icon -->
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="w-full">
                                <h3 class="text-lg font-medium text-blue-800">Your Request has been Quoted!</h3>
                                <p class="text-sm text-blue-700 mt-1">
                                    PR <span class="font-semibold">#{{ $quotedOrder->order_number }}</span> has been reviewed by the admin.
                                </p>
                                
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg border border-blue-200">
                                    <div>
                                        <span class="text-xs text-gray-500 uppercase tracking-wider">Total Price</span>
                                        <div class="text-xl font-bold text-gray-900">₱{{ number_format($quotedOrder->total, 2) }}</div>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500 uppercase tracking-wider">Estimated Delivery</span>
                                        <div class="text-xl font-bold text-gray-900">
                                            {{ $quotedOrder->delivery_date ? $quotedOrder->delivery_date->format('M d, Y') : 'TBA' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                                    <form action="{{ route('client.purchase-requests.accept', $quotedOrder) }}" method="POST" class="w-full sm:w-auto">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Accept Quotation
                                        </button>
                                    </form>

                                    <button type="button" onclick="document.getElementById('cancel-pr-modal').classList.remove('hidden')" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Cancel Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cancellation Modal -->
                    <div id="cancel-pr-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('cancel-pr-modal').classList.add('hidden')"></div>
                            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form action="{{ route('client.purchase-requests.cancel', $quotedOrder) }}" method="POST">
                                    @csrf
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="sm:flex sm:items-start">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Cancel Purchase Request</h3>
                                                <div class="mt-2">
                                                    <p class="text-sm text-gray-500">Are you sure you want to cancel this request? Please provide a reason.</p>
                                                    <textarea name="cancellation_reason" rows="3" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" required placeholder="Reason for cancellation..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Confirm Cancellation
                                        </button>
                                        <button type="button" onclick="document.getElementById('cancel-pr-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Keep Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
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
            @endif

            <!-- Dynamic PR Form: Category → Product → Size → Paper Type → Ply -->
            @if (empty($hasPending) || !$hasPending)
                <div class="mt-2 rounded-xl border border-zinc-100 bg-white p-8 shadow-sm">
                    <h2 class="text-sm md:text-base font-semibold mb-6 text-zinc-900">Select Options</h2>
                    <form method="POST" action="{{ route('client.purchase-requests.store') }}" id="dynamicPrForm" class="space-y-6" enctype="multipart/form-data">
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

                            <!-- 3. Sizes (checkbox per size) -->
                            <div>
                                <label class="block text-xs md:text-sm font-medium text-zinc-700">Sizes<span class="text-red-500"> *</span></label>
                                <div id="prSizesContainer" class="mt-2 space-y-2">
                                    <div class="text-xs text-zinc-500">Select a product to load sizes.</div>
                                </div>
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

                        <!-- Quotation (optional attachment) -->
                        <div>
                            <label class="block text-xs md:text-sm font-medium text-zinc-700">Quotation (optional)</label>
                            <input type="file" name="attachment" accept="application/pdf,image/*" class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-800 shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-[var(--color-primary)] transition" />
                            @error('attachment')
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