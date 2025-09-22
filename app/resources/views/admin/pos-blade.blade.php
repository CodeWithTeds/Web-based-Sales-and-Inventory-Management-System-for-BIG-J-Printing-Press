<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __($title ?? 'Admin POS') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="min-h-screen bg-gray-50">
                <div class="mx-auto py-4">
                    <div class="flex items-center justify-between mb-6">
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $title ?? 'Point of Sale' }}</h1>
                        <form method="GET" action="{{ route(($routePrefix ?? 'admin.pos')) }}" class="flex items-center space-x-3">
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search products..." class="w-64 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
                            <select name="category" class="rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" @selected($category===$cat)>{{ $cat }}</option>
                                @endforeach
                            </select>
                            <a href="{{ route(($routePrefix ?? 'admin.pos')) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">Reset</a>
                        </form>
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
                                            <form method="POST" action="{{ route((($routePrefix ?? 'admin.pos') . '.add'), $product->id) }}" hx-post="{{ route((($routePrefix ?? 'admin.pos') . '.add'), $product->id) }}" hx-target="#pos-cart" hx-swap="outerHTML">
                                                @csrf
                                                <button class="inline-flex items-center px-2.5 py-1.5 bg-indigo-600 text-white text-xs rounded-md hover:bg-indigo-700">Add</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full text-center text-gray-500 py-12">No products found</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Right: Order Summary -->
                        <div class="lg:col-span-1">
                            @include('admin.partials.pos-cart', [
                                'cart' => $cart,
                                'total' => $total,
                                'itemCount' => $itemCount,
                                'success' => session('success'),
                                'error' => session('error'),
                                'routePrefix' => ($routePrefix ?? 'admin.pos')
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>