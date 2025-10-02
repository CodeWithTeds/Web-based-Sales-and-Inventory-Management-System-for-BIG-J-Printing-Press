<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('My Activity Logs') }}
                </h2>
                <form method="GET" class="flex flex-wrap items-end gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="rounded-md border-gray-300 shadow-sm text-sm" />
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-wider hover:bg-indigo-700 focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Filter</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-gray-600">{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $log->method }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $log->route_name }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500 truncate max-w-[300px]" title="{{ $log->url }}">{{ $log->url }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $log->ip }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ $log->status_code ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-sm text-gray-500">No activity found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>