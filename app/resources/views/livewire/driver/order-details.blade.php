<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <a href="{{ route('driver.orders.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">Order Details</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">{{ now()->format('F j, Y') }}</span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ str_replace('_', ' ', $order->delivery_status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success Message -->
        @if (session()->has('message'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Order Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Order Information</h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Order Number</span>
                        <span class="text-sm text-gray-900">{{ $order->order_number }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Customer</span>
                        <span class="text-sm text-gray-900">{{ $order->user->name }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Email</span>
                        <span class="text-sm text-gray-900">{{ $order->user->email }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Order Date</span>
                        <span class="text-sm text-gray-900">{{ $order->created_at->format('F j, Y g:i A') }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Total Amount</span>
                        <span class="text-lg font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Status Update -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Update Delivery Status</h2>
                </div>
                
                <div class="p-6">
                    <form wire:submit.prevent="updateDeliveryStatus">
                        <div class="mb-4">
                            <label for="delivery-status" class="block text-sm font-medium text-gray-700 mb-2">
                                Current Status: <span class="font-semibold">{{ str_replace('_', ' ', $order->delivery_status) }}</span>
                            </label>
                            <select 
                                wire:model="deliveryStatus" 
                                id="delivery-status"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option value="pending">Pending</option>
                                <option value="preparing">Preparing</option>
                                <option value="out_for_delivery">Out for Delivery</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <button 
                            type="submit"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delivery Address and Map -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Delivery Location</h2>
            </div>
            
            <div class="p-6">
                @if($order->userAddress && $order->userAddress->exact_address)
                    <div class="mb-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Delivery Address</h3>
                        <p class="text-gray-900">{{ $order->userAddress->exact_address }}</p>
                        @if($order->userAddress && $order->userAddress->latitude && $order->userAddress->longitude)
                            <p class="text-sm text-gray-500 mt-2">
                                Coordinates: {{ $order->userAddress->latitude }}, {{ $order->userAddress->longitude }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="text-center text-gray-500 py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="mt-2 text-sm">No delivery address provided</p>
                    </div>
                @endif

                <!-- Map -->
                @if($order->userAddress && $order->userAddress->latitude && $order->userAddress->longitude)
                    <div id="map" data-lat="{{ optional($order->userAddress)->latitude }}" data-lng="{{ optional($order->userAddress)->longitude }}" class="w-full h-96 rounded-lg border border-gray-200" style="min-height:24rem"></div>
                @else
                    <div class="text-center text-gray-500 py-16 bg-gray-50 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <p class="mt-2 text-sm">No location coordinates available for this order</p>
                    </div>
                @endif
            </div>
        </div>

    <!-- Google Maps Script -->
    @if($order->userAddress && $order->userAddress->latitude && $order->userAddress->longitude)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function initMap() {
            if (typeof L === 'undefined') {
                console.error('Leaflet failed to load');
                return;
            }
            const el = document.getElementById('map');
            if (!el) {
                console.warn('Map element not found');
                return;
            }

            const latRaw = el?.dataset?.lat;
            const lngRaw = el?.dataset?.lng;
            const lat = typeof latRaw === 'number' ? latRaw : parseFloat(latRaw);
            const lng = typeof lngRaw === 'number' ? lngRaw : parseFloat(lngRaw);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                console.warn('Invalid coordinates', { lat, lng, latRaw, lngRaw });
                return;
            }
            if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                console.warn('Out-of-range coordinates', { lat, lng });
                return;
            }

            const orderLocation = [lat, lng];

            if (window.orderDetailsMap) {
                window.orderDetailsMap.remove();
                window.orderDetailsMap = null;
            }

            const map = window.orderDetailsMap = L.map('map').setView(orderLocation, 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            const marker = L.marker(orderLocation).addTo(map);
            const popupHtml = `
                <div style="padding: 8px;">
                    <strong>Order: {{ $order->order_number }}</strong><br>
                    <span>{{ strtoupper(str_replace('_', ' ', $order->delivery_status)) }}</span><br>
                    <small>{{ $order->userAddress ? addslashes($order->userAddress->exact_address) : 'No address provided' }}</small>
                </div>
            `;
            marker.bindPopup(popupHtml).openPopup();

            setTimeout(() => map.invalidateSize(), 100);
            map.on('resize', () => map.invalidateSize());
        }

        document.addEventListener('DOMContentLoaded', () => setTimeout(initMap, 0));
        window.addEventListener('load', () => setTimeout(initMap, 0));
        document.addEventListener('livewire:load', function () {
            setTimeout(initMap, 0);
            if (window.Livewire && typeof Livewire.hook === 'function') {
                Livewire.hook('message.processed', () => {
                    if (document.getElementById('map')) {
                        setTimeout(initMap, 0);
                    }
                });
            }
        });
    </script>
    @endif
        <!-- Order Items -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Order Items ({{ count($orderItems) }})</h2>
            </div>
            
            <div class="divide-y divide-gray-200">
                @foreach($orderItems as $item)
                    <div class="px-6 py-4 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</h3>
                            <p class="text-sm text-gray-500">Quantity: {{ $item['quantity'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-900">₱{{ number_format($item['price'], 2) }} each</p>
                            <p class="text-sm font-medium text-gray-900">₱{{ number_format($item['subtotal'], 2) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>