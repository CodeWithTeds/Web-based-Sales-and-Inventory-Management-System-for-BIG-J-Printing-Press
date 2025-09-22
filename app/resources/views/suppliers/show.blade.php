<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Supplier Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold">{{ $item->name }}</h3>
                        <div class="space-x-2">
                            <a href="{{ route('admin.suppliers.edit', $item->id) }}" class="inline-flex items-center px-3 py-1.5 bg-yellow-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">{{ __('Edit') }}</a>
                            <a href="{{ route('admin.suppliers.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">{{ __('Back to List') }}</a>
                            <form action="{{ route('admin.suppliers.destroy', $item->id) }}" method="POST" class="inline" data-confirm="{{ __('Are you sure?') }}" onsubmit="return confirm(this.dataset.confirm);">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">{{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Email') }}</h4>
                            <p class="mt-1">{{ $item->email ?: '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Phone') }}</h4>
                            <p class="mt-1">{{ $item->phone ?: '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Address') }}</h4>
                            <p class="mt-1">{{ $item->address ?: '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Status') }}</h4>
                            <p class="mt-1">
                                @if (($item->status ?? 'inactive') === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="mr-1 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        <svg class="mr-1 h-2 w-2 text-gray-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                        {{ __('Inactive') }}
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <h4 class="text-sm font-medium text-gray-500">{{ __('Notes') }}</h4>
                            <p class="mt-1 whitespace-pre-line">{{ $item->notes ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>