<x-app-layout>
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">{{ __('Category Details') }}</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.categories.edit', $item->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">{{ __('Edit') }}</a>
                <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">{{ __('Back') }}</a>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow p-6 space-y-4">
            <div>
                <div class="text-sm text-gray-500">{{ __('Name') }}</div>
                <div class="text-lg font-semibold">{{ $item->name }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Status') }}</div>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ ucfirst($item->status ?? 'inactive') }}
                </span>
            </div>
            <div>
                <div class="text-sm text-gray-500">{{ __('Notes') }}</div>
                <div class="text-lg">{{ $item->notes ?? '—' }}</div>
            </div>
        </div>
    </div>
</x-app-layout>