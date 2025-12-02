@php
    $title = __('Inventory Report');
@endphp
<x-layouts.app title="{{ $title }}">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ $title }}</h2>
        </div>
    </x-slot>

    <!-- Filters -->
    <div class="mb-6 rounded-2xl bg-white p-4 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
        <form method="get" class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label for="from" class="block text-xs text-gray-600 mb-1 dark:text-neutral-400">{{ __('From') }}</label>
                <input id="from" type="date" name="from" value="{{ request('from') }}" class="w-full rounded-md border border-neutral-300 px-2 py-2 text-xs dark:border-neutral-700 dark:bg-zinc-800 dark:text-neutral-200" />
            </div>
            <div>
                <label for="to" class="block text-xs text-gray-600 mb-1 dark:text-neutral-400">{{ __('To') }}</label>
                <input id="to" type="date" name="to" value="{{ request('to') }}" class="w-full rounded-md border border-neutral-300 px-2 py-2 text-xs dark:border-neutral-700 dark:bg-zinc-800 dark:text-neutral-200" />
            </div>
            <div class="md:col-span-2 flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-xs font-medium text-white hover:bg-emerald-700">{{ __('Apply Filters') }}</button>
                <a href="{{ route('admin.reports.inventory') }}" class="inline-flex items-center gap-2 rounded-md bg-gray-200 px-3 py-2 text-xs font-medium text-gray-800 hover:bg-gray-300 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4 mb-6">
        <div class="rounded-2xl bg-white p-4 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <p class="text-xs text-gray-600 dark:text-neutral-400">{{ __('Stock In Transactions') }}</p>
            <p class="text-2xl font-semibold text-[#D62F1A]">{{ ($stockIn ?? collect())->count() }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <p class="text-xs text-gray-600 dark:text-neutral-400">{{ __('Product Out Lines') }}</p>
            <p class="text-2xl font-semibold text-[#D62F1A]">{{ ($productOut ?? collect())->count() }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <p class="text-xs text-gray-600 dark:text-neutral-400">{{ __('Materials In Transactions') }}</p>
            <p class="text-2xl font-semibold text-[#D62F1A]">{{ ($stockOut ?? collect())->count() }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <p class="text-xs text-gray-600 dark:text-neutral-400">{{ __('Materials Out / Used (Work)') }}</p>
            <p class="text-2xl font-semibold text-[#D62F1A]">{{ ($materialsOut ?? collect())->count() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Product In -->
        <div class="rounded-2xl bg-white p-6 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-neutral-200">{{ __('Product In (stock-in)') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-neutral-400">
                            <th class="px-2 py-1">{{ __('Date') }}</th>
                            <th class="px-2 py-1">{{ __('Item') }}</th>
                            <th class="px-2 py-1 text-right">{{ __('Qty') }}</th>
                            <th class="px-2 py-1">{{ __('Unit') }}</th>
                            <th class="px-2 py-1">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($stockIn as $txn)
                            <tr class="text-gray-800 dark:text-neutral-200">
                                <td class="px-2 py-1">{{ optional($txn->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-2 py-1">{{ $txn->name ?? ucfirst($txn->subject_type) . ' #' . $txn->subject_id }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($txn->quantity, 2) }}</td>
                                <td class="px-2 py-1">{{ $txn->unit }}</td>
                                <td class="px-2 py-1 truncate max-w-[240px]">{{ $txn->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500">{{ __('No recent stock-in transactions.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Product Out -->
        <div class="rounded-2xl bg-white p-6 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-neutral-200">{{ __('Product Out (released/used)') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-neutral-400">
                            <th class="px-2 py-1">{{ __('Date') }}</th>
                            <th class="px-2 py-1">{{ __('Item') }}</th>
                            <th class="px-2 py-1 text-right">{{ __('Qty') }}</th>
                            <th class="px-2 py-1">{{ __('Unit') }}</th>
                            <th class="px-2 py-1">{{ __('Order') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($productOut as $line)
                            <tr class="text-gray-800 dark:text-neutral-200">
                                <td class="px-2 py-1">{{ optional($line->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-2 py-1">{{ $line->product->name ?? $line->name }}</td>
                                <td class="px-2 py-1 text-right">{{ (int) $line->qty }}</td>
                                <td class="px-2 py-1">{{ $line->product->unit ?? $line->unit ?? '' }}</td>
                                <td class="px-2 py-1">
                                    @if($line->order)
                                        <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('admin.orders.show', $line->order_id) }}">#{{ $line->order->order_number }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500">{{ __('No recent product-out lines.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Material In -->
        <div class="rounded-2xl bg-white p-6 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-neutral-200">{{ __('Material In (stock-in)') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-neutral-400">
                            <th class="px-2 py-1">{{ __('Date') }}</th>
                            <th class="px-2 py-1">{{ __('Material') }}</th>
                            <th class="px-2 py-1 text-right">{{ __('Qty') }}</th>
                            <th class="px-2 py-1">{{ __('Unit') }}</th>
                            <th class="px-2 py-1">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($stockOut as $txn)
                            <tr class="text-gray-800 dark:text-neutral-200">
                                <td class="px-2 py-1">{{ optional($txn->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-2 py-1">{{ $txn->name ?? 'Material #' . $txn->subject_id }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($txn->quantity, 2) }}</td>
                                <td class="px-2 py-1">{{ $txn->unit }}</td>
                                <td class="px-2 py-1 truncate max-w-[240px]">{{ $txn->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500">{{ __('No recent stock-in transactions.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Materials Out / Used (Work) -->
        <div class="rounded-2xl bg-white p-6 shadow-lg border border-gray-100 dark:border-neutral-700 dark:bg-zinc-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-neutral-200">{{ __('Materials Out / Used (Work)') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-neutral-400">
                            <th class="px-2 py-1">{{ __('Date') }}</th>
                            <th class="px-2 py-1">{{ __('Material') }}</th>
                            <th class="px-2 py-1 text-right">{{ __('Qty') }}</th>
                            <th class="px-2 py-1">{{ __('Unit') }}</th>
                            <th class="px-2 py-1">{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($materialsOut as $txn)
                            <tr class="text-gray-800 dark:text-neutral-200">
                                <td class="px-2 py-1">{{ optional($txn->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-2 py-1">{{ $txn->name ?? 'Material #' . $txn->subject_id }}</td>
                                <td class="px-2 py-1 text-right">{{ number_format($txn->quantity, 2) }}</td>
                                <td class="px-2 py-1">{{ $txn->unit }}</td>
                                <td class="px-2 py-1 truncate max-w-[240px]">{{ $txn->notes }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-2 text-center text-gray-500">{{ __('No recent stock-out transactions.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>