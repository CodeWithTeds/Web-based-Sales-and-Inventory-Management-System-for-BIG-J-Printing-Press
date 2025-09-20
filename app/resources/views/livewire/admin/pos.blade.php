<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Point of Sale</h1>
            <div class="flex items-center space-x-3">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search products..." class="w-64 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                <select wire:model="category" class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
                <button wire:click="clearCart" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Clear Cart</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Products -->
            <div class="lg:col-span-2">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @forelse($products as $product)
                        <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition p-3 flex flex-col">
                            <div class="h-32 bg-gray-100 rounded-lg overflow-hidden mb-3">
                                @if($product->image_path)
                                    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 0 0 2 2h14m-4-4 4 4m0 0-4 4m4-4H7a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ $product->category }}</p>
                            </div>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-indigo-600 font-semibold">₱{{ number_format($product->price, 2) }}</span>
                                <button wire:click="addToCart({{ $product->id }})" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700">Add</button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center text-gray-500 py-12">No products found</div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold">Order Summary</h2>
                        <span class="text-sm text-gray-500">Items: {{ collect($cart)->sum('qty') }}</span>
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
                                        <button wire:click="removeItem({{ $item['id'] }})" class="text-gray-400 hover:text-red-500" title="Remove">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="mt-2 flex items-center">
                                        <button wire:click="decrementItem({{ $item['id'] }})" class="h-7 w-7 flex items-center justify-center rounded-l bg-gray-100 hover:bg-gray-200">-</button>
                                        <input type="text" readonly class="h-7 w-12 text-center border-t border-b border-gray-200" value="{{ $item['qty'] }}" />
                                        <button wire:click="incrementItem({{ $item['id'] }})" class="h-7 w-7 flex items-center justify-center rounded-r bg-gray-100 hover:bg-gray-200">+</button>
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
                            <button wire:click="checkout" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50" @disabled(collect($cart)->isEmpty())>
                                Checkout
                            </button>
                        </div>
                        @if (session()->has('success'))
                            <div class="mt-3 text-green-600 text-sm">{{ session('success') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>