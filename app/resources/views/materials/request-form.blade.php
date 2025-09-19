<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Request Material') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Request Material</h1>
        <a href="{{ route('materials.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
            Back to Materials
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <ul>
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form action="{{ route('materials.submit-request') }}" method="POST">
            @csrf

            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Request Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="department" class="block text-gray-700 text-sm font-bold mb-2">Department:</label>
                        <input type="text" name="department" id="department" value="{{ old('department') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div>
                        <label for="requester_name" class="block text-gray-700 text-sm font-bold mb-2">Requester Name:</label>
                        <input type="text" name="requester_name" id="requester_name" value="{{ old('requester_name') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div>
                        <label for="request_date" class="block text-gray-700 text-sm font-bold mb-2">Request Date:</label>
                        <input type="date" name="request_date" id="request_date" value="{{ old('request_date', date('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div>
                        <label for="required_date" class="block text-gray-700 text-sm font-bold mb-2">Required Date:</label>
                        <input type="date" name="required_date" id="required_date" value="{{ old('required_date') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Material Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="material_id" class="block text-gray-700 text-sm font-bold mb-2">Material:</label>
                        <select name="material_id" id="material_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            <option value="">Select Material</option>
                            @foreach($materials as $material)
                            <option value="{{ $material->id }}" {{ old('material_id') == $material->id ? 'selected' : '' }}>
                                {{ $material->name }} ({{ $material->category }}) - Available: {{ $material->quantity }} {{ $material->unit }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="quantity" class="block text-gray-700 text-sm font-bold mb-2">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" min="1" step="1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>

                    <div class="md:col-span-2">
                        <label for="purpose" class="block text-gray-700 text-sm font-bold mb-2">Purpose:</label>
                        <textarea name="purpose" id="purpose" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>{{ old('purpose') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <a href="{{ route('materials.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
        </div>
    </div>
</x-app-layout>