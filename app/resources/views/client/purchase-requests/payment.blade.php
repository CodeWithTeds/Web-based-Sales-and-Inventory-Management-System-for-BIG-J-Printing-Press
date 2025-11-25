@php
    $pageTitle = 'PR Payment';
    $approved = $approvedOrder ?? null;
    $pending = $pendingOrder ?? null;
    $total = $approved ? (float) ($approved->total ?? 0) : 0.0;
    $requiredDown = $approved ? round($total * 0.10, 2) : 0.0;
    $alreadyDown = $approved ? (float) ($approved->downpayment ?? 0) : 0.0;
    $remainingDown = max(0.0, $requiredDown - $alreadyDown);
    $remainingFull = max(0.0, $total - $alreadyDown);
@endphp

<x-layouts.app :title="$pageTitle">
    <div class="max-w-6xl mx-auto space-y-8 py-8">
        <div class="relative overflow-hidden rounded-3xl shadow-sm ring-1 ring-black/5">
            <div class="p-6 md:p-8">
                <h1 class="text-base md:text-lg font-semibold text-zinc-900">{{ $pageTitle }}</h1>
                <p class="mt-2 text-xs md:text-sm text-zinc-700">View your Purchase Request payments and pay the required downpayment.</p>
            </div>
        </div>

        @if ($pending && !$approved)
            <div class="rounded-xl border border-yellow-300 bg-yellow-50 p-5">
                <div class="flex items-start gap-3">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mt-0.5 text-yellow-600"><path d="M12 9v4m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Your latest PR is pending approval.</p>
                        <p class="text-sm text-yellow-700">PR <span class="font-semibold">#{{ $pending->order_number }}</span> is waiting for admin approval. Payments will be available after approval.</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($approved)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4">
                    <div class="text-xs text-zinc-500">Approved PR</div>
                    <div class="mt-1 text-sm font-semibold text-zinc-900">#{{ $approved->order_number }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4">
                    <div class="text-xs text-zinc-500">Total</div>
                    <div class="mt-1 text-sm font-semibold text-zinc-900">₱ {{ number_format($total, 2) }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4">
                    <div class="text-xs text-zinc-500">Required Downpayment (10%)</div>
                    <div class="mt-1 text-sm font-semibold text-zinc-900">₱ {{ number_format($requiredDown, 2) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4">
                    <div class="text-xs text-zinc-500">Already Paid</div>
                    <div class="mt-1 text-sm font-semibold text-zinc-900">₱ {{ number_format($alreadyDown, 2) }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4">
                    <div class="text-xs text-zinc-500">Remaining Downpayment</div>
                    <div class="mt-1 text-sm font-semibold text-zinc-900">₱ {{ number_format($remainingDown, 2) }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 flex items-center justify-end">
                    @if ($remainingDown > 0)
                        <a href="{{ route('client.purchase-requests.paymongo.start') }}" class="inline-flex items-center rounded-lg bg-[var(--color-primary)]/90 px-4 py-2 text-sm font-medium text-white shadow-sm hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition">
                            <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12l5 5L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Pay 10% Downpayment
                        </a>
                    @else
                        <span class="text-xs md:text-sm text-green-700">Downpayment fully paid.</span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4">
                    <div class="text-xs text-zinc-500">Remaining Balance</div>
                    <div class="mt-1 text-sm font-semibold text-zinc-900">₱ {{ number_format($remainingFull, 2) }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 md:col-span-2 flex items-center justify-end">
                    @if ($remainingFull > 0)
                        <a href="{{ route('client.purchase-requests.paymongo.remaining.start') }}" class="inline-flex items-center rounded-lg bg-[var(--color-accent-brand)]/90 px-4 py-2 text-sm font-medium text-white shadow-sm hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-accent-brand)] transition">
                            <svg class="mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12l5 5L20 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Pay Remaining Balance
                        </a>
                    @else
                        <span class="text-xs md:text-sm text-green-700">Order fully paid.</span>
                    @endif
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm md:text-base font-semibold mb-4 text-zinc-900">Payment Records</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-zinc-600">
                                <th class="py-2 px-3">Reference</th>
                                <th class="py-2 px-3">Provider</th>
                                <th class="py-2 px-3">Method</th>
                                <th class="py-2 px-3">Amount</th>
                                <th class="py-2 px-3">Paid At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $p)
                                <tr class="border-b border-zinc-100">
                                    <td class="py-2 px-3 text-zinc-800">{{ $p->reference }}</td>
                                    <td class="py-2 px-3 text-zinc-800">{{ $p->provider }}</td>
                                    <td class="py-2 px-3 text-zinc-800">{{ $p->method }}</td>
                                    <td class="py-2 px-3 text-zinc-800">₱ {{ number_format($p->amount, 2) }}</td>
                                    <td class="py-2 px-3 text-zinc-800">{{ optional($p->paid_at)->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 px-3 text-zinc-600">No payments recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <p class="text-sm text-zinc-700">No approved Purchase Request found. Submit a PR and wait for approval to proceed with payment.</p>
            </div>
        @endif

        @php
            $flashMessage = session('status') ?: session('error');
        @endphp
        @if (!empty($flashMessage))
            <!-- Hook for JS module to show SweetAlert on status/errors -->
            <div id="prStatus" data-message="{{ $flashMessage }}" class="hidden"></div>
        @endif
    </div>
</x-layouts.app>