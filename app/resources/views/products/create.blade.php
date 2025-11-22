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

                        <!-- Product Name -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Product Name')" />
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
                            <select id="category" name="category" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled {{ old('category') ? '' : 'selected' }}>Select category</option>
                                @foreach(($categoryModels ?? []) as $cat)
                                    <option value="{{ $cat->name }}" data-id="{{ $cat->id }}" {{ old('category') === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <!-- Sizes -->
                        <div class="mt-4">
                            <x-input-label :value="__('Sizes')" />
                            <div id="sizes-container" class="mt-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                <p class="text-sm text-gray-500">Select a category to load sizes.</p>
                            </div>
                            <x-input-error :messages="$errors->get('size_ids')" class="mt-2" />
                        </div>

                        <div class="flex mt-4 space-x-4">
                            <!-- Price -->
                            <div class="w-1/3">
                                <x-input-label for="price" :value="__('Price')" />
                                <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', 0)" required step="0.01" min="0" />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>

                            <!-- Unit -->
                            <div class="w-1/3">
                                <x-input-label for="unit" :value="__('Unit (how product is sold)')" />
                                <select id="unit" name="unit" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @php
                                        $unitOptions = ['booklet', 'box', 'piece', 'pack', 'ream', 'set', 'sheet'];
                                    @endphp
                                    <option value="" disabled {{ old('unit') ? '' : 'selected' }}>Select unit</option>
                                    @foreach($unitOptions as $u)
                                        <option value="{{ $u }}" {{ old('unit', 'piece') === $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                            </div>

                            <!-- Quantity -->
                            <div class="w-1/3">
                                <x-input-label for="quantity" :value="__('Quantity (available stock)')" />
                                <x-text-input id="quantity" class="block mt-1 w-full" type="number" name="quantity" :value="old('quantity', 0)" required step="1" min="0" />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>

                            <!-- Status: hidden default Available -->
                            <input type="hidden" name="status" value="Available" />
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
            const categorySelect = document.getElementById('category');
            const sizesContainer = document.getElementById('sizes-container');
            const preselectedSizes = {!! json_encode(old('size_ids', [])) !!};

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

            async function loadSizesByCategoryId(categoryId) {
                if (!categoryId) {
                    sizesContainer.innerHTML = '<p class="text-sm text-gray-500">Select a category to load sizes.</p>';
                    return;
                }
                try {
                    sizesContainer.innerHTML = '<p class="text-sm text-gray-500">Loading sizes...</p>';
                    const res = await fetch(`/sizes/by-category/${categoryId}`, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    const items = Array.isArray(data.items) ? data.items : [];
                    if (!items.length) {
                        sizesContainer.innerHTML = '<p class="text-sm text-gray-500">No sizes available for this category.</p>';
                        return;
                    }
                    const fragment = document.createDocumentFragment();
                    items.forEach(size => {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'flex items-center';
                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.id = `size_${size.id}`;
                        input.name = 'size_ids[]';
                        input.value = String(size.id);
                        input.className = 'rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50';
                        if (preselectedSizes.includes(String(size.id)) || preselectedSizes.includes(Number(size.id))) {
                            input.checked = true;
                        }
                        const label = document.createElement('label');
                        label.setAttribute('for', `size_${size.id}`);
                        label.className = 'ml-2 text-sm text-gray-700';
                        label.textContent = `${size.name}`;
                        wrapper.appendChild(input);
                        wrapper.appendChild(label);
                        fragment.appendChild(wrapper);
                    });
                    sizesContainer.innerHTML = '';
                    sizesContainer.appendChild(fragment);
                } catch (e) {
                    sizesContainer.innerHTML = '<p class="text-sm text-red-600">Failed to load sizes. Please try again.</p>';
                }
            }

            // On category change, fetch sizes using selected option's data-id
            categorySelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const categoryId = option ? option.getAttribute('data-id') : null;
                loadSizesByCategoryId(categoryId);
            });

            // If a category is already selected (old input), load sizes initially
            (function initialLoad() {
                const option = categorySelect.options[categorySelect.selectedIndex];
                const categoryId = option ? option.getAttribute('data-id') : null;
                if (categoryId) {
                    loadSizesByCategoryId(categoryId);
                }
            })();
        });
    </script>
</x-app-layout>