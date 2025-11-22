<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('products.update', $item->id) }}" enctype="multipart/form-data">
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
                            <select id="category" name="category" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="" disabled>Select category</option>
                                @foreach(($categoryModels ?? []) as $cat)
                                    <option value="{{ $cat->name }}" data-id="{{ $cat->id }}" {{ old('category', $item->category) === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div class="flex mt-4 space-x-4">
                            <!-- Price -->
                            <div class="w-1/3">
                                <x-input-label for="price" :value="__('Price')" />
                                <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', $item->price)" required step="0.01" min="0" />
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>

                            <!-- Unit -->
                            <div class="w-1/3">
                                <x-input-label for="unit" :value="__('Unit (how product is sold)')" />
                                <select id="unit" name="unit" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @php
                                        $unitOptions = ['booklet', 'box', 'piece', 'pack', 'ream', 'set', 'sheet'];
                                    @endphp
                                    <option value="" disabled {{ old('unit', $item->unit) ? '' : 'selected' }}>Select unit</option>
                                    @foreach($unitOptions as $u)
                                        <option value="{{ $u }}" {{ old('unit', $item->unit ?? 'piece') === $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                            </div>

                            <!-- Quantity -->
                            <div class="w-1/3">
                                <x-input-label for="quantity" :value="__('Quantity (available stock)')" />
                                <x-text-input id="quantity" class="block mt-1 w-full" type="number" name="quantity" :value="old('quantity', $item->quantity)" required step="1" min="0" />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="Available" {{ old('status', $item->status ?? 'Available') === 'Available' ? 'selected' : '' }}>Available</option>
                                <option value="Unavailable" {{ old('status', $item->status) === 'Unavailable' ? 'selected' : '' }}>Unavailable</option>
                                <option value="Phase Out" {{ old('status', $item->status) === 'Phase Out' ? 'selected' : '' }}>Phase Out</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <!-- Current Image -->
                        @if($item->image_path)
                        <div class="mt-4">
                            <x-input-label :value="__('Current Image')" />
                            <div class="mt-2">
                                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}" class="h-32 w-32 object-cover rounded border border-gray-200">
                            </div>
                        </div>
                        @endif

                        <!-- Image Upload -->
                        <div class="mt-4">
                            <x-input-label for="image" :value="__('Update Product Image')" />
                            <input id="image" name="image" type="file" class="block mt-1 w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                                focus:outline-none" />
                            <p class="mt-1 text-sm text-gray-500">Upload a new product image (optional). Max 2MB. Supported formats: JPG, PNG, GIF.</p>
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <!-- Notes -->
                        <div class="mt-4">
                            <x-input-label for="notes" :value="__('Notes')" />
                            <textarea id="notes" name="notes" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $item->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Sizes -->
                        <div class="mt-6">
                            <x-input-label :value="__('Sizes')" />
                            <p class="text-xs text-gray-500 mb-2">Select applicable sizes for this product. Options depend on the selected category.</p>
                            <div id="sizeCheckboxesContainer" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                <!-- Size checkboxes will be injected here -->
                            </div>
                            <x-input-error :messages="$errors->get('size_ids')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function() {
            const categorySelect = document.getElementById('category');
            const container = document.getElementById('sizeCheckboxesContainer');
            const preselected = {!! json_encode(old('size_ids', ($item->sizes ?? collect())->pluck('id')->all())) !!};

            function renderSizes(sizes) {
                container.innerHTML = '';
                if (!sizes || sizes.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-sm">No sizes available for the selected category.</p>';
                    return;
                }
                sizes.forEach(size => {
                    const isChecked = preselected && preselected.includes(Number(size.id));
                    const wrapper = document.createElement('label');
                    wrapper.className = 'inline-flex items-center space-x-2 px-3 py-2 border rounded-md hover:bg-gray-50';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'size_ids[]';
                    checkbox.value = size.id;
                    checkbox.checked = !!isChecked;
                    checkbox.className = 'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500';

                    const span = document.createElement('span');
                    span.textContent = size.name;

                    wrapper.appendChild(checkbox);
                    wrapper.appendChild(span);

                    container.appendChild(wrapper);
                });
            }

            async function loadSizes(categoryId) {
                if (!categoryId) {
                    renderSizes([]);
                    return;
                }
                try {
                    const res = await fetch(`/sizes/by-category/${categoryId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!res.ok) throw new Error('Failed to fetch sizes');
                    const json = await res.json();
                    renderSizes((Array.isArray(json.items) ? json.items : (json.sizes || [])));
                } catch (e) {
                    console.error(e);
                    renderSizes([]);
                }
            }

            function getSelectedCategoryId() {
                const opt = categorySelect.options[categorySelect.selectedIndex];
                return opt ? opt.getAttribute('data-id') : null;
            }

            // Initial load
            loadSizes(getSelectedCategoryId());

            // On category change
            categorySelect.addEventListener('change', () => {
                loadSizes(getSelectedCategoryId());
            });
        })();
    </script>
</x-app-layout>