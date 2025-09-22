<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Material') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('materials.update', $item->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $item->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $item->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Category')" />
                            <x-text-input id="category" class="block mt-1 w-full" type="text" name="category" :value="old('category', $item->category)" required />
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div class="flex mt-4 space-x-4">
                            <!-- Unit -->
                            <div class="w-1/2">
                                <x-input-label for="unit" :value="__('Unit')" />
                                <x-text-input id="unit" class="block mt-1 w-full" type="text" name="unit" :value="old('unit', $item->unit)" required />
                                <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                            </div>

                            <!-- Quantity -->
                            <div class="w-1/2">
                                <x-input-label for="quantity" :value="__('Quantity')" />
                                <x-text-input id="quantity" class="block mt-1 w-full" type="number" name="quantity" :value="old('quantity', $item->quantity)" required step="0.01" min="0" />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex mt-4 space-x-4">
                            <!-- Reorder Level -->
                            <div class="w-1/2">
                                <x-input-label for="reorder_level" :value="__('Reorder Level')" />
                                <x-text-input id="reorder_level" class="block mt-1 w-full" type="number" name="reorder_level" :value="old('reorder_level', $item->reorder_level)" required min="0" />
                                <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
                            </div>

                            <!-- Unit Price -->
                            <div class="w-1/2">
                                <x-input-label for="unit_price" :value="__('Unit Price')" />
                                <x-text-input id="unit_price" class="block mt-1 w-full" type="number" name="unit_price" :value="old('unit_price', $item->unit_price)" required step="0.01" min="0" />
                                <x-input-error :messages="$errors->get('unit_price')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Supplier -->
                        <div class="mt-4">
                            <x-input-label for="supplier" :value="__('Supplier')" />
                            <select id="supplier" name="supplier" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('Select a supplier') }}</option>
                                @if(isset($suppliers) && count($suppliers))
                                    @php $selectedSupplier = old('supplier', $item->supplier); @endphp
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->name }}" @selected($selectedSupplier == $supplier->name)>{{ $supplier->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <x-input-error :messages="$errors->get('supplier')" class="mt-2" />
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $item->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('materials.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Material') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>