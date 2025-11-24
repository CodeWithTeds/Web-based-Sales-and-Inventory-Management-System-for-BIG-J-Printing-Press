<x-layouts.app :title="__('Sales Projection')">
    @php
        $productsArr = $products ?? [];
        $weeksArr = $weeks ?? [];
        $totalProjectedAmount = array_sum(array_map(fn($p) => $p['projected_amount'] ?? 0, $productsArr));
        $totalAvgQty = array_sum(array_map(fn($p) => $p['avg_qty'] ?? 0, $productsArr));
        $totalAvgAmount = array_sum(array_map(fn($p) => $p['avg_amount'] ?? 0, $productsArr));
    @endphp

    <div class="p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">{{ __('Sales Projection') }}</h2>
            <form method="GET" action="{{ route('admin.sales-projection.index') }}" class="flex items-center gap-2">
                <label for="weeks" class="text-xs text-gray-600 dark:text-neutral-300">{{ __('Weeks') }}</label>
                <select id="weeks" name="weeks" class="rounded-md border border-neutral-300 px-2 py-1 text-xs dark:border-neutral-700 dark:bg-zinc-800 dark:text-neutral-200">
                    @foreach([4,8,12] as $w)
                        <option value="{{ $w }}" @selected(($selectedWeeks ?? 8) == $w)>{{ $w }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">{{ __('Apply') }}</button>
            </form>
        </div>

        <!-- Metrics cards -->
        <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-indigo-50 to-white p-4 shadow-sm dark:border-neutral-700 dark:from-zinc-900 dark:to-zinc-800">
                <div class="text-xs text-gray-500 dark:text-neutral-400">{{ __('Products in projection') }}</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-neutral-100">{{ number_format(count($productsArr)) }}</div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm dark:border-neutral-700 dark:from-zinc-900 dark:to-zinc-800">
                <div class="text-xs text-gray-500 dark:text-neutral-400">{{ __('Total Projected Sales') }}</div>
                <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-neutral-100">₱{{ number_format($totalProjectedAmount, 2) }}</div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-gradient-to-br from-sky-50 to-white p-4 shadow-sm dark:border-neutral-700 dark:from-zinc-900 dark:to-zinc-800">
                <div class="text-xs text-gray-500 dark:text-neutral-400">{{ __('Total Weekly Averages') }}</div>
                <div class="mt-1 text-sm text-gray-700 dark:text-neutral-300">{{ __('Qty') }}: {{ number_format($totalAvgQty, 2) }}</div>
                <div class="text-sm text-gray-700 dark:text-neutral-300">{{ __('Sales') }}: ₱{{ number_format($totalAvgAmount, 2) }}</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ __('Weekly Total Qty') }}</h3>
                    <span class="text-[11px] text-gray-500 dark:text-neutral-400">{{ __('Period') }}: {{ implode(', ', $weeksArr) }}</span>
                </div>
                <div class="mt-3">
                    <canvas id="weeklyQtyChart" height="120"></canvas>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ __('Top Products by Projected Sales') }}</h3>
                    <span class="text-[11px] text-gray-500 dark:text-neutral-400">{{ __('Top 5') }}</span>
                </div>
                <div class="mt-3">
                    <canvas id="topProductsChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Table with pagination -->
        <div class="mt-6">
            <div class="flex items-center justify-between gap-2">
                <div id="paginationInfo" class="text-xs text-gray-600 dark:text-neutral-400"></div>
                <div class="flex items-center gap-2">
                    <label for="perPage" class="text-xs text-gray-600 dark:text-neutral-400">{{ __('Items per page') }}</label>
                    <select id="perPage" class="rounded-md border border-neutral-300 px-2 py-1 text-xs dark:border-neutral-700 dark:bg-zinc-800 dark:text-neutral-200">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="mt-2 overflow-x-auto rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-neutral-400">
                            <th class="px-3 py-2">{{ __('Product') }}</th>
                            <th class="px-3 py-2">{{ __('Weekly Avg Qty') }}</th>
                            <th class="px-3 py-2">{{ __('Weekly Avg Sales') }}</th>
                            <th class="px-3 py-2">{{ __('Projected Qty') }}</th>
                            <th class="px-3 py-2">{{ __('Projected Sales') }}</th>
                            <th class="px-3 py-2">{{ __('Last Weeks Qty') }}</th>
                        </tr>
                    </thead>
                    <tbody id="productsTbody" class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse(($productsArr) as $p)
                            <tr class="text-gray-800 dark:text-neutral-200">
                                <td class="px-3 py-2">{{ $p['product_name'] }}</td>
                                <td class="px-3 py-2">{{ number_format($p['avg_qty'] ?? 0, 2) }}</td>
                                <td class="px-3 py-2">₱{{ number_format($p['avg_amount'] ?? 0, 2) }}</td>
                                <td class="px-3 py-2 font-semibold">₱{{ number_format($p['projected_amount'] ?? 0, 2) }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1">
                                        @foreach(($weeksArr) as $wk)
                                            <div class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-zinc-800 text-[10px]">{{ $p['weekly_qty'][$wk] ?? 0 }}</div>
                                        @endforeach
                                    </div>
                                    <div class="mt-1 text-[10px] text-gray-500">{{ __('Weeks') }}: {{ implode(', ', $weeksArr) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-gray-500">{{ __('No sales data found for the selected period.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="pagination" class="mt-3 flex items-center justify-center gap-2"></div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            const products = @json($productsArr);
            const weeks = @json($weeksArr);
            const currency = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });
            const numberFmt = new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 });

            // Charts
            try {
                const weeklyTotals = weeks.map(w => products.reduce((sum, p) => sum + (p?.weekly_qty?.[w] ?? 0), 0));

                const ctxLine = document.getElementById('weeklyQtyChart');
                if (ctxLine && typeof Chart !== 'undefined') {
                    new Chart(ctxLine, {
                        type: 'line',
                        data: {
                            labels: weeks,
                            datasets: [{
                                label: 'Total Qty',
                                data: weeklyTotals,
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.15)',
                                tension: 0.35,
                                fill: true,
                                pointRadius: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: true },
                                tooltip: { enabled: true }
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }

                const topProducts = [...products]
                    .sort((a, b) => (b?.projected_amount ?? 0) - (a?.projected_amount ?? 0))
                    .slice(0, 5);
                const ctxBar = document.getElementById('topProductsChart');
                if (ctxBar && typeof Chart !== 'undefined') {
                    new Chart(ctxBar, {
                        type: 'bar',
                        data: {
                            labels: topProducts.map(p => p.product_name),
                            datasets: [{
                                label: 'Projected Sales',
                                data: topProducts.map(p => p?.projected_amount ?? 0),
                                backgroundColor: '#10b981'
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (ctx) => currency.format(ctx.parsed.y)
                                    }
                                }
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            } catch (e) {
                console.warn('Chart rendering failed:', e);
            }

            // Client-side pagination
            const tbody = document.getElementById('productsTbody');
            const pagination = document.getElementById('pagination');
            const info = document.getElementById('paginationInfo');
            const perPageSel = document.getElementById('perPage');
            let currentPage = 1;
            let perPage = parseInt(perPageSel?.value || '25', 10);

            function renderTable(page = 1) {
                const total = products.length;
                const start = (page - 1) * perPage;
                const end = Math.min(start + perPage, total);
                const pageData = products.slice(start, end);

                if (!tbody) return;
                if (pageData.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-3 py-4 text-center text-gray-500">{{ __('No sales data found for the selected period.') }}</td></tr>`;
                } else {
                    tbody.innerHTML = pageData.map(p => {
                        const avgQty = numberFmt.format(p?.avg_qty ?? 0);
                        const avgAmt = currency.format(p?.avg_amount ?? 0);
                        const projAmt = currency.format(p?.projected_amount ?? 0);
                        const weeksBadges = weeks.map(w => `<div class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-zinc-800 text-[10px]">${numberFmt.format(p?.weekly_qty?.[w] ?? 0)}</div>`).join('');

                        return `
                            <tr class="text-gray-800 dark:text-neutral-200">
                                <td class="px-3 py-2">${p.product_name}</td>
                                <td class="px-3 py-2">${avgQty}</td>
                                <td class="px-3 py-2">${avgAmt}</td>
                                <td class="px-3 py-2 font-semibold">${projAmt}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-1">${weeksBadges}</div>
                                    <div class="mt-1 text-[10px] text-gray-500">{{ __('Weeks') }}: ${weeks.join(', ')}</div>
                                </td>
                            </tr>`;
                    }).join('');
                }

                if (info) {
                    info.textContent = `${start + 1}-${end} / ${total}`;
                }
                renderPagination(total);
            }

            function renderPagination(total) {
                if (!pagination) return;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                const btnClass = 'px-2 py-1 text-xs rounded-md border border-neutral-300 dark:border-neutral-700 hover:bg-gray-50 dark:hover:bg-zinc-800';

                let html = '';
                html += `<button class="${btnClass}" ${currentPage === 1 ? 'disabled' : ''} data-page="prev">{{ __('Prev') }}</button>`;
                // Show up to 5 page buttons around current
                const start = Math.max(1, currentPage - 2);
                const end = Math.min(totalPages, currentPage + 2);
                for (let p = start; p <= end; p++) {
                    const active = p === currentPage ? 'bg-indigo-600 text-white border-indigo-600' : '';
                    html += `<button class="${btnClass} ${active}" data-page="${p}">${p}</button>`;
                }
                html += `<button class="${btnClass}" ${currentPage === totalPages ? 'disabled' : ''} data-page="next">{{ __('Next') }}</button>`;
                pagination.innerHTML = html;

                pagination.querySelectorAll('button').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const p = btn.getAttribute('data-page');
                        const totalPages = Math.max(1, Math.ceil(products.length / perPage));
                        if (p === 'prev' && currentPage > 1) currentPage--;
                        else if (p === 'next' && currentPage < totalPages) currentPage++;
                        else if (!isNaN(parseInt(p))) currentPage = parseInt(p);
                        renderTable(currentPage);
                    });
                });
            }

            if (perPageSel) {
                perPageSel.addEventListener('change', () => {
                    perPage = parseInt(perPageSel.value, 10);
                    currentPage = 1;
                    renderTable(currentPage);
                });
            }

            // Initialize
            renderTable(currentPage);
        })();
    </script>
</x-layouts.app>