<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Physical Inventory') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if (session('success'))
                        <div class="mb-4 rounded-md bg-green-50 p-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Page identifier (outside the table) -->
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('Physical Inventory') }}</h3>
                    </div>

                    <!-- Search filter -->
                    <form action="{{ route('admin.physical-inventory.index') }}" method="GET" class="mb-4">
                        <div class="flex items-center gap-2">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search products...') }}" class="block w-64 rounded-md border-gray-300 text-sm" />
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-semibold hover:bg-indigo-700">{{ __('Search') }}</button>
                            @if(request()->filled('q'))
                                <a href="{{ route('admin.physical-inventory.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md text-xs font-semibold hover:bg-gray-200">{{ __('Clear') }}</a>
                            @endif
                        </div>
                        @if(request()->filled('q'))
                            <p class="mt-2 text-xs text-gray-500">{{ __('Showing results for') }}: <span class="font-medium text-gray-700">{{ request('q') }}</span></p>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Image') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product Name') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('System Quantity') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Physical Count') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Remarks') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($items as $item)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $thumb = $item->image_path ? \Illuminate\Support\Facades\Storage::url($item->image_path) : asset('images/no-image.png');
                                            @endphp
                                            <img src="{{ $thumb }}" alt="{{ $item->name }}" class="h-10 w-10 rounded object-cover">
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <input type="number" value="{{ $item->physical_count ?? '' }}" class="block w-24 rounded-md border-gray-300 text-sm" readonly>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <input type="text" value="{{ $item->notes }}" class="block w-48 rounded-md border-gray-300 text-sm" readonly>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <flux:modal.trigger name="pi-{{ $item->id }}">
                                                <button class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-semibold hover:bg-indigo-700">{{ __('View') }}</button>
                                            </flux:modal.trigger>
                                        </td>
                                    </tr>

                                    <flux:modal name="pi-{{ $item->id }}" class="w-full max-w-6xl">
                                        <div class="p-6">
                                            <div class="flex flex-col sm:flex-row sm:items-start gap-6">
                                                @php
                                                    $img = $item->image_path ? \Illuminate\Support\Facades\Storage::url($item->image_path) : asset('images/no-image.png');
                                                @endphp
                                                <img src="{{ $img }}" alt="{{ $item->name }}" class="h-72 w-72 rounded object-cover flex-shrink-0 border border-gray-300">
                                                <div class="flex-1 space-y-4">
                                                    <div class="border border-gray-300 rounded-md p-4 bg-white">
                                                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Product Name') }}</div>
                                                        <div class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $item->name }}</div>
                                                    </div>
                                                    <div class="grid sm:grid-cols-2 gap-4">
                                                        <div class="border border-gray-300 rounded-md p-3 bg-white">
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('System Quantity') }}</div>
                                                            <div class="text-lg font-semibold text-gray-900">{{ $item->quantity }}</div>
                                                        </div>
                                                        @if(!empty($item->unit))
                                                            <div class="border border-gray-300 rounded-md p-3 bg-white">
                                                                <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Unit') }}</div>
                                                                <div class="text-lg font-semibold text-gray-900">{{ $item->unit }}</div>
                                                            </div>
                                                        @endif
                                                        @if(!empty($item->category))
                                                            <div class="border border-gray-300 rounded-md p-3 bg-white">
                                                                <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Category') }}</div>
                                                                <div class="text-lg font-semibold text-gray-900">{{ $item->category }}</div>
                                                            </div>
                                                        @endif
                                                        @if(!is_null($item->price))
                                                            <div class="border border-gray-300 rounded-md p-3 bg-white">
                                                                <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Price') }}</div>
                                                                <div class="text-lg font-semibold text-gray-900">₱{{ number_format($item->price, 2) }}</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    @if(!empty($item->description))
                                                        <div class="border border-gray-300 rounded-md p-4 bg-white">
                                                            <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Description') }}</div>
                                                            <p class="mt-1 text-sm text-gray-700">{{ $item->description }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <form action="{{ route('admin.physical-inventory.update', $item) }}" method="POST" class="mt-6">
                                                @csrf
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                    <div>
                                                        <label for="physical_count_{{ $item->id }}" class="block text-base font-medium text-gray-800">{{ __('Physical Count') }}</label>
                                                        <input id="physical_count_{{ $item->id }}" name="physical_count" type="number" min="0" value="{{ old('physical_count', $item->physical_count ?? $item->quantity) }}" class="mt-2 block w-full rounded-md border-gray-300 text-base p-3">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label for="remarks_{{ $item->id }}" class="block text-base font-medium text-gray-800">{{ __('Remarks') }}</label>
                                                        <input id="remarks_{{ $item->id }}" name="remarks" type="text" value="{{ old('remarks', $item->notes) }}" placeholder="{{ __('Notes about counting, discrepancies, etc.') }}" class="mt-2 block w-full rounded-md border-gray-300 text-base p-3">
                                                    </div>
                                                </div>

                                                <div class="mt-6 flex items-center justify-end gap-2">
                                                    <flux:modal.close>
                                                        <button type="button" class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md text-xs font-semibold hover:bg-gray-200">{{ __('Close') }}</button>
                                                    </flux:modal.close>
                                                    <flux:modal.close>
                                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700">{{ __('Save & Close') }}</button>
                                                    </flux:modal.close>
                                                </div>
                                            </form>
                                        </div>
                                    </flux:modal>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No products found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>