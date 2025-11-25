@php
$pageTitle = 'Choose Category';
@endphp

<x-layouts.app :title="$pageTitle">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- Hero header -->
        <div class="rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ $pageTitle }}</h1>
                    <p class="text-sm opacity-90">Create a purchase request in two steps.</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-90" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 7a2 2 0 012-2h3a2 2 0 012 2v1h4V7a2 2 0 012-2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3v2h3a2 2 0 012 2v3a2 2 0 01-2 2h-3a2 2 0 01-2-2v-1h-4v1a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 012-2h3v-2H5a2 2 0 01-2-2V7z" />
                </svg>
            </div>
        </div>

        <!-- Status / progress bar -->
        @php
        $isPending = !empty($hasPending) && $hasPending;
        $hasApproved = !empty($approvedOrder);
        @endphp
        <div class="w-full">
            <div class="flex items-center justify-between text-sm font-semibold mb-2">
                <span class="{{ ($isPending || $hasApproved) ? 'text-slate-400' : 'text-indigo-700' }}">1 • Choose Category</span>
                <span class="{{ ($isPending || $hasApproved) ? 'text-slate-400' : 'text-indigo-700' }}">2 • Select Products</span>
                @if($isPending)
                <span class="text-indigo-700">3 • Waiting for Approval</span>
                @elseif($hasApproved)
                <span class="text-indigo-700">3 • Payment Pending</span>
                @else
                <span class="text-slate-400">3 • Waiting for Approval</span>
                @endif
            </div>
            <div class="h-3 w-full rounded-full bg-slate-200 overflow-hidden border border-slate-300">
                @php
                $barWidth = '25%';
                if ($isPending) { $barWidth = '100%'; }
                if ($hasApproved) { $barWidth = '100%'; }
                @endphp
                <div class="h-3 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 transition-all duration-500" style="width: {{ $barWidth }}"></div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg">
            @if(!empty($hasPending) && $hasPending)
            <div class="rounded-xl border border-amber-300 bg-amber-50 text-amber-900 p-4 mb-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-amber-200 text-amber-900 text-sm font-bold">PR</span>
                    <div class="flex-1">
                        <div class="text-sm">
                            Waiting for Approval: <span class="font-semibold">#{{ $pendingOrder->order_number ?? '' }}</span>
                        </div>
                        <div class="text-xs text-amber-800">You can submit a new one after the current PR is processed.</div>
                    </div>
                    @if(!empty($pendingOrder))
                    <a href="{{ route('client.orders.show', $pendingOrder) }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">View PR</a>
                    @endif
                </div>
            </div>
            <div class="text-sm text-slate-600">Category selection is disabled while a PR is pending.</div>
            @elseif(!empty($approvedOrder))
            @php
            $orderTotal = (float) ($approvedOrder->total ?? 0);
            $requiredDown = round($orderTotal * 0.10, 2);
            $alreadyDown = (float) ($approvedOrder->downpayment ?? 0);
            $downRemaining = max($requiredDown - $alreadyDown, 0);
            @endphp
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-900 p-4 mb-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-200 text-emerald-900 text-sm font-bold">PR</span>
                    <div class="flex-1">
                        <div class="text-sm">
                            Approved: <span class="font-semibold">#{{ $approvedOrder->order_number }}</span>
                        </div>
                        <div class="text-xs">Total: ₱{{ number_format($orderTotal, 2) }} • Required Downpayment (10%): ₱{{ number_format($requiredDown, 2) }}</div>
                        @if($downRemaining > 0.001)
                        <div class="text-xs">Outstanding Downpayment: <span class="font-semibold">₱{{ number_format($downRemaining, 2) }}</span></div>
                        @else
                        <div class="text-xs">Downpayment paid. Thank you!</div>
                        @endif
                    </div>
                        <div class="flex items-center gap-2">
                            @if($downRemaining > 0.001)
                            <a href="{{ route('client.purchase-requests.paymongo.start') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Pay 10% via GCash</a>
                            @endif
                            <a href="{{ route('client.orders.show', $approvedOrder) }}" class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-slate-900">View Order</a>
                        </div>
                </div>
            </div>
            <!-- Even with an approved PR, you may create a new PR -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold">Select a Category</h2>
                <div class="w-64">
                    <input type="text" id="categorySearch" class="block w-full rounded-md border-slate-300 text-sm" placeholder="Search categories...">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="categoriesGrid">
                @forelse ($categories as $c)
                <a href="{{ route('client.purchase-requests.create', $c) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 hover:border-indigo-300 hover:shadow-lg transition">
                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-indigo-50 to-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 text-sm font-semibold">
                        {{ mb_substr($c->name, 0, 2) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-slate-900">{{ $c->name }}</div>
                        <div class="text-xs text-slate-500">Tap to select</div>
                    </div>
                    <div class="text-xs text-indigo-600">Select</div>
                </a>
                @empty
                <div class="text-sm text-slate-600">No active categories available.</div>
                @endforelse
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const search = document.getElementById('categorySearch');
                    const grid = document.getElementById('categoriesGrid');
                    search?.addEventListener('input', function() {
                        const q = (search.value || '').toLowerCase();
                        grid.querySelectorAll('a').forEach(a => {
                            const nameEl = a.querySelector('.text-sm.font-semibold, .text-sm.font-medium');
                            const name = (nameEl?.textContent || '').toLowerCase();
                            a.style.display = name.includes(q) ? '' : 'none';
                        });
                    });
                });
            </script>
            @else
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold">Select a Category</h2>
                <div class="w-64">
                    <input type="text" id="categorySearch" class="block w-full rounded-md border-slate-300 text-sm" placeholder="Search categories...">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="categoriesGrid">
                @forelse ($categories as $c)
                <a href="{{ route('client.purchase-requests.create', $c) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 hover:border-indigo-300 hover:shadow-lg transition">
                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-indigo-50 to-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 text-sm font-semibold">
                        {{ mb_substr($c->name, 0, 2) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold text-slate-900">{{ $c->name }}</div>
                        <div class="text-xs text-slate-500">Tap to select</div>
                    </div>
                    <div class="text-xs text-indigo-600">Select</div>
                </a>
                @empty
                <div class="text-sm text-slate-600">No active categories available.</div>
                @endforelse
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const search = document.getElementById('categorySearch');
                    const grid = document.getElementById('categoriesGrid');
                    search?.addEventListener('input', function() {
                        const q = (search.value || '').toLowerCase();
                        grid.querySelectorAll('a').forEach(a => {
                            const nameEl = a.querySelector('.text-sm.font-semibold, .text-sm.font-medium');
                            const name = (nameEl?.textContent || '').toLowerCase();
                            a.style.display = name.includes(q) ? '' : 'none';
                        });
                    });
                });
            </script>
            @endif
        </div>
    </div>
</x-layouts.app>