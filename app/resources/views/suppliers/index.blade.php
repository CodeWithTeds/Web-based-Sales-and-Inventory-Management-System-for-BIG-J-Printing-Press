<x-app-layout>
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">{{ __('Suppliers') }}</h1>
            <a href="{{ route('admin.suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">+ {{ __('Add Supplier') }}</a>
        </div>

        @isset($metrics)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow p-4">
                <div class="text-sm text-gray-500">{{ __('Total Suppliers') }}</div>
                <div class="text-2xl font-semibold">{{ $metrics['total_suppliers'] ?? 0 }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow p-4">
                <div class="text-sm text-gray-500">{{ __('Active') }}</div>
                <div class="text-2xl font-semibold">{{ $metrics['active_suppliers'] ?? 0 }}</div>
            </div>
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow p-4">
                <div class="text-sm text-gray-500">{{ __('Inactive') }}</div>
                <div class="text-2xl font-semibold">{{ $metrics['inactive_suppliers'] ?? 0 }}</div>
            </div>
        </div>
        @endisset

        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Phone') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-zinc-800">
                @forelse($items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $item->phone }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($item->status ?? 'inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.suppliers.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</a>
                            <a href="{{ route('admin.suppliers.edit', $item->id) }}" class="text-blue-600 hover:text-blue-900">{{ __('Edit') }}</a>
                            <form action="{{ route('admin.suppliers.destroy', $item->id) }}" method="POST" class="inline" data-confirm="{{ __('Are you sure?') }}" onsubmit="return confirm(this.dataset.confirm);">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ __('No suppliers found.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $items->links() }}</div>
        </div>
    </div>
</x-app-layout>