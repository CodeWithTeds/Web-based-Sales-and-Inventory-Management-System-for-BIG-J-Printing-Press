<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Manage Materials for') }}: {{ $product->name }}
                </h2>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('products.show', $product->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to Product') }}
                    </a>
                </div>
            </div>

            <!-- Session Messages -->
            @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Current Materials -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Current Materials</h3>
                            
                            @if($product->materials->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($product->materials as $material)
                                                <tr>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $material->name }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $material->pivot->quantity }} {{ $material->unit }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                        ₱{{ number_format($material->unit_price * $material->pivot->quantity, 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                        <form action="{{ route('products.materials.remove', [$product->id, $material->id]) }}" method="POST" class="inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to remove this material?')">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="2" class="px-4 py-3 text-right text-sm font-medium text-gray-900">Total Material Cost:</td>
                                                <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-900">
                                                    ₱{{ number_format($product->materials->sum(function($material) {
                                                        return $material->unit_price * $material->pivot->quantity;
                                                    }), 2) }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">No materials added to this product yet.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Add Material Form -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Add Material</h3>
                            
                            <form id="add-material-form" method="POST" action="{{ route('products.materials.add', $product->id) }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <x-input-label :value="__('Materials')" />
                                    <div class="mt-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                        @foreach($availableMaterials as $material)
                                        <div class="flex items-center">
                                            <input type="checkbox" id="material_{{ $material->id }}" name="material_ids[]" value="{{ $material->id }}" data-unit="{{ $material->unit }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ $material->quantity <= 0 ? 'disabled' : '' }}>
                                            <label for="material_{{ $material->id }}" class="ml-2 text-sm {{ $material->quantity <= 0 ? 'text-gray-400' : 'text-gray-700' }}">
                                                {{ $material->name }} 
                                                <span class="{{ $material->quantity <= 0 ? 'text-red-500 font-medium' : 'text-gray-500' }}">
                                                    ({{ $material->quantity }} {{ $material->unit }} available{{ $material->quantity <= 0 ? ' - Out of Stock' : '' }})
                                                </span>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>

                                    @error('material_ids')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div style="display:none;">
                                    @error('quantities')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                    @error('quantities.*')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <x-primary-button>
                                        {{ __('Add Material') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const materialCheckboxes = document.querySelectorAll('input[name="material_ids[]"]');
            const addMaterialForm = document.getElementById('add-material-form');
            
            if (!addMaterialForm) return;
            
            // Add hidden input for each selected material with default quantity 1
            materialCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const materialId = this.value;
                    const hiddenInputName = `quantities[${materialId}]`;
                    
                    // Remove existing hidden input for this material if exists (within the add form only)
                    const existingInput = addMaterialForm.querySelector(`input[name="${hiddenInputName}"]`);
                    if (existingInput) {
                        existingInput.remove();
                    }
                    
                    // If checkbox is checked, create hidden input with default quantity 1
                    if (this.checked) {
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = hiddenInputName;
                        hiddenInput.value = '1'; // Default quantity
                        addMaterialForm.appendChild(hiddenInput);
                    }
                });
            });
        });
    </script>
</x-app-layout>