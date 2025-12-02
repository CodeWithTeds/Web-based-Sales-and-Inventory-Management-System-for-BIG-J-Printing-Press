<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Details') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-6 space-y-6 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Order No.</div>
                        <div class="text-lg font-mono">{{ $order->order_number }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="flex items-center justify-end space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">{{ $order->status }}</span>
                            @php $ordersRoutePrefix = request()->routeIs('staff.orders.*') ? 'staff.orders' : 'admin.orders'; @endphp
                            <form method="POST" action="{{ route($ordersRoutePrefix . '.delivery.update', $order) }}">
                                @csrf
                                @method('PUT')
                                <div class="inline-flex items-center space-x-2">
                                    <select name="delivery_status" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($deliveryStatuses as $status)
                                            <option value="{{ $status }}" @selected($order->delivery_status === $status)>{{ \Illuminate\Support\Str::of($status)->replace('_',' ')->title() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="inline-flex items-center px-2 py-1 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @if (session('status'))
                    <div class="p-3 bg-green-50 text-green-700 rounded">{{ session('status') }}</div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                        <h3 class="text-sm font-semibold text-gray-700">Customer</h3>
                        <div class="text-gray-800">{{ $order->customer_name ?? $order->user->name ?? '—' }}</div>
                    </div>
                    <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                        <h3 class="text-sm font-semibold text-gray-700">Payment</h3>
                        <div class="text-sm text-gray-600">Reference: {{ optional($order->payments->last())->reference ?? '—' }}</div>
                        <div class="text-sm text-gray-600">Total: ₱{{ number_format($order->total, 2) }}</div>
                    </div>
                </div>

                @php
                    $lastPaymentMethod = optional($order->payments->last())->method;
                    $isGcash = $lastPaymentMethod && strcasecmp($lastPaymentMethod, 'GCASH') === 0;
                @endphp

                @if($address && $isGcash)
                <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Address</h3>
                    <div class="space-y-1 text-sm">
                        <div><span class="text-gray-500">Exact:</span> <span class="text-gray-800">{{ $address->exact_address }}</span></div>
                        <div><span class="text-gray-500">Barangay:</span> <span class="text-gray-800">{{ $addressNames['barangay_name'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500">City / Municipality:</span> <span class="text-gray-800">{{ $addressNames['city_name'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500">Province:</span> <span class="text-gray-800">{{ $addressNames['province_name'] ?? '—' }}</span></div>
                        <div><span class="text-gray-500">Region:</span> <span class="text-gray-800">{{ $addressNames['region_name'] ?? '—' }}</span></div>
                    </div>
                </div>
                @endif

                @if(!empty($order->attachment_path))
                <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Attachment</h3>
                    <a href="{{ Storage::url($order->attachment_path) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">View Attachment</a>
                </div>
                @endif
                </div>

                <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                            <thead class="bg-gray-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-sm">{{ $item->name }}</td>
                                        <td class="px-3 py-2 text-sm">{{ $item->qty }}</td>
                                        <td class="px-3 py-2 text-sm">₱{{ number_format($item->price, 2) }}</td>
                                        <td class="px-3 py-2 text-sm">₱{{ number_format($item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($order->status === 'pending')
                <div class="mt-6 border border-gray-200 dark:border-zinc-700 rounded-lg p-6 bg-white dark:bg-zinc-900">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Approve Purchase Request</h3>
                    @if($errors->has('approve'))
                        <div class="p-3 bg-red-50 text-red-700 rounded mb-3 border border-red-200">{{ $errors->first('approve') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="p-3 bg-red-50 text-red-700 rounded mb-3 border border-red-200">
                            {{ __('Please fix the errors and try again.') }}
                        </div>
                    @endif

                    @php
                        $materialAgg = [];
                        foreach ($order->items as $it) {
                            if ($it->product) {
                                foreach ($it->product->materials as $m) {
                                    $required = (float) $m->pivot->quantity * (int) $it->qty;
                                    if (!isset($materialAgg[$m->id])) {
                                        $materialAgg[$m->id] = [
                                            'name' => $m->name,
                                            'unit' => $m->unit,
                                            'available' => (float) $m->quantity,
                                            'required' => 0.0,
                                        ];
                                    }
                                    $materialAgg[$m->id]['required'] += $required;
                                }
                            }
                        }
                    @endphp

                    <form method="POST" action="{{ route('admin.orders.approve', $order) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                                <label class="block text-sm font-medium text-gray-700">Delivery Date</label>
                                <input type="date" name="delivery_date" value="{{ old('delivery_date', $order->delivery_date ? (is_string($order->delivery_date) ? $order->delivery_date : optional($order->delivery_date)->format('Y-m-d')) : '') }}" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                                @error('delivery_date')
                                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Set Item Prices</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                                    <thead class="bg-gray-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td class="px-3 py-2 text-sm">{{ $item->name }}
                                                <input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}">
                                            </td>
                                            <td class="px-3 py-2 text-sm">{{ $item->qty }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                <input type="number" step="0.01" min="0" name="items[{{ $item->id }}][price]" value="{{ old('items.' . $item->id . '.price', $item->price) }}" class="w-32 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required aria-label="Unit price for {{ $item->name }}">
                                                @error('items.' . $item->id . '.price')
                                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="border border-gray-200 dark:border-zinc-700 rounded-md p-4 bg-white dark:bg-zinc-900">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Allocate Materials</h4>
                            <p class="text-xs text-gray-500 mb-3">Defaults are computed from product-material mappings; adjust as needed. You can also add materials not mapped to the products.</p>

                            <div class="mb-4 flex items-end gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs font-medium text-gray-700">Add Material</label>
                                    <select id="addMaterialSelect" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                        <option value="">— Select material —</option>
                                        @foreach($materials as $mat)
                                            <option value="{{ $mat->id }}" data-unit="{{ $mat->unit }}" data-available="{{ (float) $mat->quantity }}">{{ $mat->name }} ({{ $mat->unit }}) — Available: {{ number_format((float) $mat->quantity, 2) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-64">
                                    <label class="block text-xs font-medium text-gray-700">Associate to Product</label>
                                    <select id="addMaterialProductSelect" class="mt-1 block w-full rounded-md border-gray-300 text-sm">
                                        <option value="">— Select product —</option>
                                        @foreach($order->items as $it)
                                            @if($it->product)
                                                <option value="{{ $it->product->id }}" data-qty="{{ (int) $it->qty }}">{{ $it->product->name }} (Qty: {{ $it->qty }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button type="button" id="addMaterialBtn" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700">Add</button>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                                    <thead class="bg-gray-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocate Qty</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="materialsTableBody" class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                                        @forelse($materialAgg as $mid => $m)
                                        <tr data-material-id="{{ $mid }}">
                                            <td class="px-3 py-2 text-sm">{{ $m['name'] }} <span class="text-xs text-gray-500">({{ $m['unit'] }})</span>
                                                <input type="hidden" name="materials[{{ $mid }}][id]" value="{{ $mid }}">
                                            </td>
                                            <td class="px-3 py-2 text-sm">
                                                <input type="number" step="0.01" min="0" name="materials[{{ $mid }}][required]" value="{{ old('materials.' . $mid . '.required', $m['required']) }}" class="w-32 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                                @error('materials.' . $mid . '.required')
                                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 text-sm">{{ number_format($m['available'], 2) }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                <input type="number" step="0.01" min="0" name="materials[{{ $mid }}][qty]" value="{{ old('materials.' . $mid . '.qty', $m['required']) }}" class="w-32 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                                @error('materials.' . $mid . '.qty')
                                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-400">—</td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-3 py-2 text-sm text-gray-600">No material mappings found for items.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Approve Request</button>
                        </div>
                    </form>
                </div>
                @endif

                <div class="flex items-center justify-between">
                    <a href="{{ route($ordersRoutePrefix . '.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Back to Orders</a>
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('admin.pos.receipt', $order) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">View Receipt</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const addBtn = document.getElementById('addMaterialBtn');
        const select = document.getElementById('addMaterialSelect');
        const productSelect = document.getElementById('addMaterialProductSelect');
        const tbody = document.getElementById('materialsTableBody');

        function ensureRow(id, name, unit, available, productId) {
            const existing = tbody.querySelector('tr[data-material-id="' + id + '"]');
            if (existing) {
                const hidden = existing.querySelector('input[name="materials[' + id + '][product_id]"]');
                if (hidden) hidden.value = productId || '';
                return;
            }
            const tr = document.createElement('tr');
            tr.setAttribute('data-material-id', id);
            tr.innerHTML = `
                <td class="px-3 py-2 text-sm">${name} <span class="text-xs text-gray-500">(${unit || ''})</span>
                    <input type="hidden" name="materials[${id}][id]" value="${id}">
                    <input type="hidden" name="materials[${id}][product_id]" value="${productId || ''}">
                </td>
                <td class="px-3 py-2 text-sm">
                    <input type="number" step="0.01" min="0" name="materials[${id}][required]" value="0" class="w-32 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </td>
                <td class="px-3 py-2 text-sm">${Number(available).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                <td class="px-3 py-2 text-sm">
                    <input type="number" step="0.01" min="0" name="materials[${id}][qty]" value="0" class="w-32 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </td>
                <td class="px-3 py-2 text-sm">
                    <button type="button" class="text-red-600 hover:underline" data-remove-id="${id}">Remove</button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        if (addBtn && select && productSelect && tbody) {
            addBtn.addEventListener('click', () => {
                const id = parseInt(select.value, 10);
                const pid = parseInt(productSelect.value, 10);
                if (!id) {
                    alert('Select a material to add.');
                    return;
                }
                if (!pid) {
                    alert('Select the product this material belongs to.');
                    return;
                }
                const opt = select.options[select.selectedIndex];
                const name = opt.text.split(' — ')[0]; // name (unit)
                const unit = opt.dataset.unit || '';
                const available = opt.dataset.available || 0;
                ensureRow(id, name, unit, available, pid);
                select.value = '';
                productSelect.value = '';
            });

            tbody.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-id]');
                if (!btn) return;
                const id = btn.getAttribute('data-remove-id');
                const tr = tbody.querySelector('tr[data-material-id="' + id + '"]');
                if (tr) tr.remove();
            });
        }
    });
    </script>
</x-app-layout>