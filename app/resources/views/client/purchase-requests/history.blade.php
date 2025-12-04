<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Request History') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-zinc-900 shadow-sm sm:rounded-lg p-6 space-y-6 border border-gray-200 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700">Your Purchase Requests</h3>
                        <p class="text-xs text-gray-500">List of all PRs you created.</p>
                    </div>
                    <div>
                        <a href="{{ route('client.purchase-requests.select-category') }}" class="inline-flex items-center rounded-lg bg-[var(--color-primary)]/90 px-3 py-2 text-sm font-medium text-white shadow-sm hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] transition">New Purchase Request</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                        <thead class="bg-gray-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No.</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Date</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-700">
                            @forelse($orders as $order)
                                <tr>
                                    <td class="px-3 py-2 text-sm font-mono">{{ $order->order_number }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">{{ $order->status }}</span>
                                        @if($order->status === 'cancelled' && $order->cancellation_reason)
                                            <div class="text-xs text-red-600 mt-1 max-w-xs break-words" title="{{ $order->cancellation_reason }}">
                                                Reason: {{ \Illuminate\Support\Str::limit($order->cancellation_reason, 50) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-sm">₱{{ number_format((float) ($order->total ?? 0), 2) }}</td>
                                    <td class="px-3 py-2 text-sm">{{ is_string($order->delivery_date) ? $order->delivery_date : optional($order->delivery_date)->format('Y-m-d') }}</td>
                                    <td class="px-3 py-2 text-sm">{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <a href="{{ route('client.orders.show', $order) }}" class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">View</a>
                                        @php
                                            $total = (float) ($order->total ?? 0);
                                            $paidTotal = (float) ($order->downpayment ?? 0);
                                            $remaining = max(0, round($total - $paidTotal, 2));
                                        @endphp
                                        @if($order->status === 'approved' && $remaining > 0)
                                            <a href="{{ route('client.purchase-requests.paymongo.remaining.start', ['order_id' => $order->id]) }}"
                                               class="ml-2 inline-flex items-center px-3 py-1 bg-[var(--color-primary)]/90 text-white rounded-md hover:brightness-95">
                                                Pay Remaining (₱{{ number_format($remaining, 2) }})
                                            </a>
                                        @elseif($order->status === 'approved' && $remaining <= 0)
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs">Fully Paid</span>
                                        @elseif($order->status === 'quoted')
                                            <div class="inline-flex items-center ml-2 space-x-2">
                                                <form action="{{ route('client.purchase-requests.accept', $order) }}" method="POST" class="inline js-accept-form">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs">Accept</button>
                                                </form>
                                                <button type="button" class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs js-cancel-button" data-action-url="{{ route('client.purchase-requests.cancel', $order) }}" onclick="openCancelModal(this.dataset.actionUrl)">Cancel</button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">No Purchase Requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation Modal -->
    <div id="cancelModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-40" aria-hidden="true" onclick="closeCancelModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="cancelForm" action="" method="POST">
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
                        <button type="button" onclick="closeCancelModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Fallback modal helpers (in case external JS fails to load) -->
    <script>
        window.openCancelModal = window.openCancelModal || function(actionUrl){
            var form = document.getElementById('cancelForm');
            var modal = document.getElementById('cancelModal');
            if (form) form.setAttribute('action', actionUrl);
            if (modal) modal.classList.remove('hidden');
        };
        window.closeCancelModal = window.closeCancelModal || function(){
            var modal = document.getElementById('cancelModal');
            if (modal) modal.classList.add('hidden');
        };
    </script>

    <!-- JS: SweetAlert + page script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/pr-history.js') }}"></script>
 </x-app-layout>
