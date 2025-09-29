<div class="p-6">
    @if (session('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200">
            <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar: Orders List -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Active Orders</h2>
                </div>
                <div class="p-4 space-y-3">
                    @php $ordersList = $orders ?? []; @endphp
                    @forelse ($ordersList as $order)
                        @php
                            $status = $order['delivery_status'] ?? ($order['status'] ?? 'pending');
                            $statusLabel = strtoupper(str_replace('_', ' ', $status));
                        @endphp
                        <div class="rounded-md border border-gray-200 p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-500">Order</p>
                                    <p class="text-base font-semibold text-gray-900">{{ $order['order_number'] ?? $order['id'] }}</p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full" data-status="{{ $status }}">{{ $statusLabel }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600 truncate">{{ $order['exact_address'] ?? 'No address' }}</p>
                            <div class="mt-3">
                                <a href="{{ route('driver.orders.show', ['order' => $order['id']]) }}" class="view-details-btn w-full inline-flex justify-center px-3 py-2 text-sm rounded-md border border-gray-300 hover:bg-gray-50">View Details</a>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No active orders found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Orders Map</h2>
                </div>
                <div class="p-4">
                    <div id="orders-map" class="w-full h-[500px] rounded-lg border border-gray-200" data-order-show-url-template="{{ route('driver.orders.show', ['order' => 'ORDER_ID']) }}"></div>
                    {{-- Map Controls sidebar and toggle removed as requested --}}
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@2.0.0-alpha.1/dist/leaflet.css" crossorigin="" />
<style>
    /* Basic badge coloring via status classes applied by JS */
    .status-badge { border: 1px solid currentColor; background-color: #fff; }
    /* Map sidebar styles (sidebar removed) */
    #map-sidebar.open { transform: translateX(0); }
</style>
@endpush

@push('scripts')
<script type="importmap">
{
  "imports": {
    "leaflet": "https://unpkg.com/leaflet@2.0.0-alpha.1/dist/leaflet.js"
  }
}
</script>
<script type="application/json" id="orders-data">{!! json_encode($orders ?? []) !!}</script>
<script type="module" src="/js/orders-map.js"></script>
@endpush