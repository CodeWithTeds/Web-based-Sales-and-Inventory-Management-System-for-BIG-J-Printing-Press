@php
    $pageTitle = 'Select Products';
@endphp

<x-layouts.app :title="$pageTitle">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold">{{ $pageTitle }}</h1>
            @if (session('status'))
                <span class="text-xs px-2 py-1 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">{{ session('status') }}</span>
            @endif
        </div>

        <!-- Progress bar: Step 2 of 3 (bigger line) -->
        <div class="w-full">
            <div class="flex items-center justify-between text-sm font-medium text-slate-700 mb-2">
                <span>1 • Choose Category</span>
                <span>2 • Select Products</span>
                <span>3 • Waiting for Admin Approval</span>
            </div>
            <div class="h-4 w-full rounded-full bg-slate-200 overflow-hidden">
                <!-- Step 2: align fill with the second label → 50% -->
                <div class="h-4 bg-indigo-600" style="width: 50%"></div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3 text-sm text-slate-600">Category: <span class="font-medium">{{ $category->name }}</span></div>
            <form method="POST" action="{{ route('client.purchase-requests.store') }}" class="space-y-6" id="prForm" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium text-slate-700">Purpose / Note</label>
                        <textarea name="purpose" rows="2" class="mt-1 block w-full rounded-md border-slate-300 text-sm" placeholder="Describe why these items are needed" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium text-slate-700">Quotation (optional)</label>
                        <input type="file" name="attachment" accept="application/pdf,image/*" class="mt-1 block w-full rounded-md border-slate-300 text-sm" />
                        @error('attachment')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-sm font-semibold">Select Items and Quantities</h2>

                    <!-- Products list for selected category -->
                    <div id="productsList" class="space-y-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @forelse ($products as $p)
                                <div class="flex items-center gap-3 rounded border border-slate-200 p-2">
                                    <input type="checkbox" class="rounded" data-item-type="products" data-item-id="{{ $p->id }}">
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded border border-slate-200 bg-slate-50">
                                        @php $thumb = $p->image_path ? \Illuminate\Support\Facades\Storage::url($p->image_path) : asset('images/logo.png'); @endphp
                                        <img src="{{ $thumb }}" alt="{{ $p->name }}" class="h-full w-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium">{{ $p->name }}</div>
                                        <div class="text-xs text-slate-500">Category: {{ $p->category ?? '—' }}</div>
                                    </div>
                                    <div class="w-24">
                                        <input type="number" min="1" class="qty-input mt-1 block w-full rounded-md border-slate-300 text-sm" placeholder="Qty" data-item-type="products" data-item-id="{{ $p->id }}">
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-600">No products found for this category.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <input type="hidden" name="items" id="itemsPayload">

                <div class="flex items-center justify-end gap-2">
                    <button type="button" id="resetBtn" class="inline-flex items-center rounded-md bg-slate-100 px-3 py-2 text-xs font-medium text-slate-700 hover:bg-slate-200">Reset</button>
                    <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-medium text-white hover:bg-indigo-700">Save & Send for Review</button>
                </div>
            </form>
        </div>
    </div>

    @if ($errors->any())
        <!-- Hook for JS module to show the first server-side validation error -->
        <div id="prFormErrors" data-message="{{ $errors->first() }}" class="hidden"></div>
    @endif

    <script>
    document.addEventListener('DOMContentLoaded', function(){
        const form = document.getElementById('prForm');
        const payload = document.getElementById('itemsPayload');
        const resetBtn = document.getElementById('resetBtn');
        const qtyInputs = document.querySelectorAll('.qty-input');
        const checkboxes = document.querySelectorAll('input[type="checkbox"][data-item-type]');

        function gatherItems(){
            const items = [];
            checkboxes.forEach(cb => {
                const type = cb.getAttribute('data-item-type');
                const id = parseInt(cb.getAttribute('data-item-id') || '0', 10);
                const qtyInput = document.querySelector(`.qty-input[data-item-type="${type}"][data-item-id="${id}"]`);
                const qty = parseInt(qtyInput?.value || '0', 10);
                if (cb.checked && id > 0 && qty > 0) {
                    items.push({ type, id, qty });
                }
            });
            return items;
        }

        form.addEventListener('submit', function(e){
            const items = gatherItems();
            payload.value = JSON.stringify(items);
        });

        resetBtn.addEventListener('click', function(){
            checkboxes.forEach(cb => { cb.checked = false; });
            qtyInputs.forEach(inp => { inp.value = ''; });
            payload.value = '';
        });

        // If server returned errors, show first error via alert
        const err = document.getElementById('prFormErrors');
        if (err && err.dataset.message) {
            alert(err.dataset.message);
        }
    });
    </script>
</x-layouts.app>