<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Material Details') }}
            </h2>
            <div class="flex space-x-4">
                <a href="{{ route('materials.edit', $item->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <a href="{{ route('materials.stock-in.form', $item->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Stock In') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('materials.index') }}" class="text-blue-600 hover:text-blue-900">
                            &larr; Back to Materials List
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Name</h4>
                                <p class="text-base">{{ $item->name }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Category</h4>
                                <p class="text-base">{{ $item->category }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Description</h4>
                                <p class="text-base">{{ $item->description ?: 'No description provided' }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Supplier</h4>
                                <p class="text-base">{{ $item->supplier ?: 'No supplier specified' }}</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Inventory Information</h3>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Current Quantity</h4>
                                <p class="text-base">{{ $item->quantity }} {{ $item->unit }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Reorder Level</h4>
                                <p class="text-base">{{ $item->reorder_level }} {{ $item->unit }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Unit Price</h4>
                                <p class="text-base">₱{{ number_format($item->unit_price, 2) }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Total Value</h4>
                                <p class="text-base">₱{{ number_format($item->getTotalValue(), 2) }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-500">Status</h4>
                                @if ($item->isLowStock())
                                    <span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Low Stock</span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">In Stock</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($item->notes)
                        <div class="mt-6 bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                            <p class="text-base">{{ $item->notes }}</p>
                        </div>
                    @endif

                    <div class="mt-6 flex justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Created: {{ $item->created_at->format('M d, Y H:i') }}</p>
                            <p class="text-sm text-gray-500">Last Updated: {{ $item->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                        
                        <form method="POST" action="{{ route('materials.destroy', $item->id) }}" onsubmit="return confirm('Are you sure you want to delete this material?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete Material') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>