@php
    $pageTitle = 'Select Category';
@endphp

<x-layouts.app :title="$pageTitle">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold">{{ $pageTitle }}</h1>
        </div>

        <!-- Progress bar: Step 1 of 3 (bigger line, start with no highlight) -->
        <div class="w-full">
            <div class="flex items-center justify-between text-sm font-medium text-slate-700 mb-2">
                <span>1 • Choose Category</span>
                <span>2 • Select Products</span>
                <span>3 • Waiting for Admin Approval</span>
            </div>
            <div class="h-4 w-full rounded-full bg-slate-200 overflow-hidden">
                <div class="h-4 bg-indigo-600" style="width: 0%"></div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-600 mb-3">Pick a category to request products from.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse ($categories as $cat)
                    <a href="{{ route('staff.purchase-requests.create', $cat->id) }}" class="block rounded border border-slate-200 p-3 hover:border-indigo-400 hover:shadow-sm transition">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">{{ $cat->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">{{ $cat->status }}</span>
                        </div>
                        @if (!empty($cat->notes))
                            <div class="mt-1 text-xs text-slate-500">{{ $cat->notes }}</div>
                        @endif
                    </a>
                @empty
                    <div class="text-sm text-slate-600">No active categories found.</div>
                @endforelse
            </div>
        </div>
    </div>
    @if (session('status'))
        <!-- Hook for JS module to show SweetAlert (Step 3) -->
        <div id="prStatus" data-message="{{ session('status') }}" class="hidden"></div>
    @endif
</x-layouts.app>