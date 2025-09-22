<div id="pos-cart" class="bg-white rounded-xl shadow-sm p-4">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Order Summary</h2>
        <span class="text-sm text-gray-500">Items: {{ $itemCount }}</span>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($cart as $item)
            <div class="py-3 flex items-center">
                <div class="h-12 w-12 rounded bg-gray-100 overflow-hidden mr-3">
                    @if(!empty($item['image_path']))
                        <img src="{{ Storage::url($item['image_path']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 0 0 2 2h14m-4-4 4 4m0 0-4 4m4-4H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10" />
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium">{{ $item['name'] }}</p>
                            <p class="text-xs text-gray-500">₱{{ number_format($item['price'], 2) }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <form method="POST" action="{{ route((($routePrefix ?? 'admin.pos') . '.remove'), $item['id']) }}" hx-delete="{{ route((($routePrefix ?? 'admin.pos') . '.remove'), $item['id']) }}" hx-target="#pos-cart" hx-swap="outerHTML">
                                @csrf
                                @method('DELETE')
                                <button class="h-7 w-7 flex items-center justify-center rounded bg-red-100 hover:bg-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                            <div class="mt-2 flex items-center">
                                <form method="POST" action="{{ route((($routePrefix ?? 'admin.pos') . '.decrement'), $item['id']) }}" hx-patch="{{ route((($routePrefix ?? 'admin.pos') . '.decrement'), $item['id']) }}" hx-target="#pos-cart" hx-swap="outerHTML">
                                    @csrf
                                    @method('PATCH')
                                    <button class="h-7 w-7 flex items-center justify-center rounded-l bg-gray-100 hover:bg-gray-200">-</button>
                                </form>
                                <input type="text" readonly class="h-7 w-12 text-center border-t border-b border-gray-200" value="{{ $item['qty'] }}" />
                                <form method="POST" action="{{ route((($routePrefix ?? 'admin.pos') . '.increment'), $item['id']) }}" hx-post="{{ route((($routePrefix ?? 'admin.pos') . '.increment'), $item['id']) }}" hx-target="#pos-cart" hx-swap="outerHTML">
                                    @csrf
                                    @method('PATCH')
                                    <button class="h-7 w-7 flex items-center justify-center rounded-r bg-gray-100 hover:bg-gray-200">+</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500 py-10">Cart is empty</div>
        @endforelse
    </div>

    <div class="mt-4 border-t pt-4">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">Subtotal</span>
            <span class="font-semibold">₱{{ number_format($total, 2) }}</span>
        </div>

        <div class="mt-3">
            <label for="customer_name" class="block text-sm text-gray-700 mb-1">Customer Name</label>
            <input id="customer_name" name="customer_name" type="text" class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" form="pos-checkout-form" placeholder="Enter customer name (optional)">
        </div>

        <div class="mt-3 flex items-center gap-2">
            <form id="pos-checkout-form" method="POST" action="{{ route((($routePrefix ?? 'admin.pos') . '.checkout')) }}" hx-post="{{ route((($routePrefix ?? 'admin.pos') . '.checkout')) }}" hx-include="#customer_name" hx-target="#pos-cart" hx-swap="outerHTML">
                @csrf
                <button class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700" @disabled(empty($cart))>
                    Checkout
                </button>
            </form>
            <form method="POST" action="{{ route((($routePrefix ?? 'admin.pos') . '.clear')) }}" hx-post="{{ route((($routePrefix ?? 'admin.pos') . '.clear')) }}" hx-target="#pos-cart" hx-swap="outerHTML">
                @csrf
                @method('DELETE')
                <button class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    Clear Cart
                </button>
            </form>
        </div>
        @php($flashSuccess = $success ?? session('success'))
        @if (!empty($flashSuccess))
            <div class="mt-3 text-green-600 text-sm">{{ $flashSuccess }}
                @php($oid = $orderId ?? session('orderId'))
                @if (!empty($oid))
                    <a href="{{ route((($routePrefix ?? 'admin.pos') . '.receipt'), $oid) }}" class="ml-2 underline text-indigo-600 hover:text-indigo-800">View Receipt</a>
                    <a href="{{ route((($routePrefix ?? 'admin.pos') . '.receipt.download'), $oid) }}" class="ml-2 underline text-indigo-600 hover:text-indigo-800">Download PDF</a>
                @endif
            </div>
        @endif
        @isset($error)
            <div class="mt-3 text-red-600 text-sm">{{ $error }}</div>
        @endisset
    </div>
</div>