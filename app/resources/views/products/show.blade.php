<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Product Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h3 class="text-lg font-semibold">{{ $item->name }}</h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('products.materials.form', ['product' => $item->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                {{ __('Manage Materials') }}
                            </a>
                            <a href="{{ route('products.edit', $item->id) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ __('Edit') }}
                            </a>
                            <a href="{{ route('products.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                {{ __('Back to List') }}
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Product Image -->
                        <div class="col-span-1">
                            @if($item->image_path)
                                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}" class="w-full h-auto rounded-lg shadow-sm">
                            @else
                                <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Product Details -->
                        <div class="col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                    <p class="mt-1">{{ $item->description ?: 'No description provided' }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Category</h4>
                                    <p class="mt-1">{{ $item->category }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Price</h4>
                                    <p class="mt-1 font-semibold">₱{{ number_format($item->price, 2) }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Status</h4>
                                    <p class="mt-1">
                                        @if ($item->active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="mr-1 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            Active
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="mr-1 h-2 w-2 text-gray-500" fill="currentColor" viewBox="0 0 8 8">
                                                <circle cx="4" cy="4" r="3" />
                                            </svg>
                                            Inactive
                                        </span>
                                        @endif
                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Material Cost</h4>
                                    <p class="mt-1">₱{{ number_format($item->calculateMaterialCost(), 2) }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500">Profit Margin</h4>
                                    @php
                                        $materialCost = $item->calculateMaterialCost();
                                        $margin = $materialCost > 0 ? (($item->price - $materialCost) / $item->price) * 100 : 100;
                                    @endphp
                                    <p class="mt-1 {{ $margin < 30 ? 'text-red-600' : ($margin < 50 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ number_format($margin, 2) }}%
                                    </p>
                                </div>

                                <div class="col-span-2">
                                    <h4 class="text-sm font-medium text-gray-500">Notes</h4>
                                    <p class="mt-1">{{ $item->notes ?: 'No notes provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Required Materials</h3>
                        
                        @if($item->materials->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Required</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($item->materials as $material)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $material->name }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $material->pivot->quantity }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">{{ $material->unit }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($material->unit_price, 2) }}</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">₱{{ number_format($material->unit_price * $material->pivot->quantity, 2) }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-500">{{ $material->pivot->notes ?: '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="4" class="px-3 py-2 text-right text-sm font-medium text-gray-900">Total Material Cost:</td>
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">₱{{ number_format($item->calculateMaterialCost(), 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="bg-gray-50 rounded-md p-4 text-center">
                                <p class="text-gray-500">No materials have been added to this product yet.</p>
                                <a href="{{ route('products.materials.form', ['product' => $item->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    {{ __('Add Materials') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>