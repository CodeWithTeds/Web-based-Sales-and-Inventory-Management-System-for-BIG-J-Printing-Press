<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Category')" />
                            <x-text-input id="category" class="block mt-1 w-full" type="text" name="category" :value="old('category')" required />
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div class="flex mt-4 space-x-4">
                            <!-- Price -->
                            <div class="w-1/2">
                                <x-input-label for="price" :value="__('Price')" />
                                <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', 0)" required step="0.01" min="0" />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>

                            <!-- Active Status -->
                            <div class="w-1/2">
                                <x-input-label for="active" :value="__('Status')" />
                                <select id="active" name="active" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <x-input-error :messages="$errors->get('active')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="mt-4">
                            <x-input-label for="image" :value="__('Product Image')" />
                            <input id="image" name="image" type="file" class="block mt-1 w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                                focus:outline-none" />
                            <p class="mt-1 text-sm text-gray-500">Upload a product image (optional). Max 2MB. Supported formats: JPG, PNG, GIF.</p>
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Materials -->
                        <div class="mt-4">
                            <x-input-label :value="__('Materials')" />
                            <div class="mt-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($materials as $material)
                                <div class="flex items-center">
                                    <input type="checkbox" id="material_{{ $material->id }}" name="material_ids[]" value="{{ $material->id }}" data-unit="{{ $material->unit }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <label for="material_{{ $material->id }}" class="ml-2 text-sm text-gray-700">{{ $material->name }} ({{ $material->quantity }} {{ $material->unit }} available)</label>
                                </div>
                                @endforeach
                            </div>

                            <x-input-error :messages="$errors->get('material_ids')" class="mt-2" />
                        </div>



                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Create Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const materialCheckboxes = document.querySelectorAll('input[name="material_ids[]"]');
            
            // Add hidden input for each selected material with default quantity 1
            materialCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const materialId = this.value;
                    const hiddenInputName = `quantities[${materialId}]`;
                    
                    // Remove existing hidden input for this material if exists
                    const existingInput = document.querySelector(`input[name="${hiddenInputName}"]`);
                    if (existingInput) {
                        existingInput.remove();
                    }
                    
                    // If checkbox is checked, create hidden input with default quantity 1
                    if (this.checked) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = hiddenInputName;
                        hiddenInput.value = '1'; // Default quantity
                        document.querySelector('form').appendChild(hiddenInput);
                    }
                });
            });
        });
    </script>
</x-app-layout>