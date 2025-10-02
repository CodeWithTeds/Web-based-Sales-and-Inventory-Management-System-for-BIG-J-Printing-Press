<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-zinc-800 sm:rounded-lg">
                <div class="p-6 text-zinc-900 dark:text-zinc-100">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <h2 class="text-xl font-semibold leading-tight">
                            {{ __('Activity Logs') }}
                        </h2>
                    </div>

                    <div class="mt-6 overflow-hidden border border-zinc-200 dark:border-zinc-700 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('Time') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('User') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('Role') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('Method') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('Route') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('URL') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('IP') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-300">
                                        {{ __('Status') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-800">
                                @forelse ($logs as $log)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $log->user->name ?? 'Unknown' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                                {{ $log->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                                   ($log->role === 'staff' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                   'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200') }}">
                                                {{ ucfirst($log->role) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                                {{ $log->method === 'GET' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                   ($log->method === 'POST' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                   ($log->method === 'PUT' || $log->method === 'PATCH' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                                   'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200')) }}">
                                                {{ $log->method }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $log->route_name }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ Str::limit($log->url, 30) }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $log->ip }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 
                                                {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 
                                                   ($log->status_code >= 300 && $log->status_code < 400 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                                   'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                                {{ $log->status_code }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ __('No activity logs found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>