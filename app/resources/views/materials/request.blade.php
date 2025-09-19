<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request Material') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 p-4 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('materials.request.submit') }}" class="space-y-6">
                        @csrf

                        <!-- Request Details Section -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Request Details</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Department -->
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                    <input type="text" name="department" id="department" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('department') }}" required>
                                </div>

                                <!-- Requester Name -->
                                <div>
                                    <label for="requester_name" class="block text-sm font-medium text-gray-700 mb-1">Requester Name</label>
                                    <input type="text" name="requester_name" id="requester_name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('requester_name') }}" required>
                                </div>

                                <!-- Request Date -->
                                <div>
                                    <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1">Request Date</label>
                                    <input type="date" name="request_date" id="request_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('request_date', date('Y-m-d')) }}" required>
                                </div>

                                <!-- Required Date -->
                                <div>
                                    <label for="required_date" class="block text-sm font-medium text-gray-700 mb-1">Required Date</label>
                                    <input type="date" name="required_date" id="required_date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('required_date') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Material Selection Section -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Material Selection</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Material -->
                                <div>
                                    <label for="material_id" class="block text-sm font-medium text-gray-700 mb-1">Material</label>
                                    <select name="material_id" id="material_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">Select a material</option>
                                        @foreach ($materials as $material)
                                            <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                                                {{ $material->name }} ({{ $material->category }}) - {{ $material->quantity }} {{ $material->unit }} available
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity Requested</label>
                                    <input type="number" name="quantity" id="quantity" min="1" step="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="{{ old('quantity', 1) }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Purpose Section -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Purpose</h3>
                            
                            <div>
                                <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose of Request</label>
                                <textarea name="purpose" id="purpose" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>{{ old('purpose') }}</textarea>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('materials.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-medium text-xs text-gray-700 uppercase tracking-wider hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>