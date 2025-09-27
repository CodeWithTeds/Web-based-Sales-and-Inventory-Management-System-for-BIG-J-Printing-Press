<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Driver Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="flex items-center space-x-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-white text-lg font-bold">
                                    {{ $item->initials() }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $item->name }}</h3>
                                    <p class="mt-1 text-sm text-gray-600">{{ __('Role') }}: <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/20">{{ ucfirst($item->role) }}</span></p>
                                </div>
                            </div>
                            <div class="mt-4 space-y-2">
                                <p class="text-sm text-gray-600"><span class="font-medium text-gray-700">{{ __('Email') }}:</span> {{ $item->email }}</p>
                                <p class="text-sm text-gray-600"><span class="font-medium text-gray-700">{{ __('Username') }}:</span> {{ $item->username ?? '—' }}</p>
                            </div>
                        </div>
                        <div>
                            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                <h4 class="text-sm font-semibold text-gray-700">{{ __('Actions') }}</h4>
                                <div class="mt-3 flex flex-wrap gap-3">
                                    <a href="{{ route('admin.drivers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Back to list') }}
                                    </a>
                                    <a href="{{ route('admin.drivers.edit', $item->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Edit Driver') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>