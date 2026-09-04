<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Audit Log</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">When</th>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Event</th>
                            <th class="px-6 py-3">Context</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-6 py-3 text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-3">{{ $log->user->name ?? 'system' }}</td>
                                <td class="px-6 py-3">{{ $log->event }}</td>
                                <td class="px-6 py-3 text-gray-500 font-mono text-xs">{{ json_encode($log->context) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                No activity yet.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
