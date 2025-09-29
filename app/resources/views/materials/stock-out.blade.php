<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Stock Out - ') }} {{ $item->name }}
        </h2>
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

                    <div class="mb-6 bg-amber-50 p-4 rounded-lg">
                        <div class="flex justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $item->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $item->category }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Current Stock: <span class="font-bold">{{ $item->quantity }} {{ $item->unit }}</span></p>
                                <p class="text-sm text-gray-600">Reorder Level: {{ $item->reorder_level }} {{ $item->unit }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('materials.stock-out', $item->id) }}">
                        @csrf

                        <!-- Quantity to Deduct -->
                        <div class="mt-4">
                            <x-input-label for="quantity" :value="__('Quantity to Deduct')" />
                            <x-text-input id="quantity" class="block mt-1 w-full" type="number" name="quantity" :value="old('quantity')" required autofocus step="0.01" min="0.01" />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('materials.show', $item->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Deduct Stock') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>