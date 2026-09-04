<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tickets</h2>
            <a href="{{ route('tickets.create') }}" class="rounded-md bg-indigo-600 text-white text-sm px-4 py-2 hover:bg-indigo-500">New ticket</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <form method="GET" action="{{ route('tickets.index') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 flex gap-3">
                <input type="text" name="q" value="{{ $q }}" placeholder="Search subjects&hellip;"
                       class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                <button class="rounded-md bg-gray-800 text-white text-sm px-4 py-2 hover:bg-gray-700">Search</button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Ticket</th>
                            <th class="px-6 py-3">Requester</th>
                            <th class="px-6 py-3">Assigned</th>
                            <th class="px-6 py-3">Priority</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                <td class="px-6 py-4">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                        #{{ $ticket->id }} {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $ticket->requester->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $ticket->assignedAgent->name ?? 'Unassigned' }}</td>
                                <td class="px-6 py-4 capitalize text-gray-600 dark:text-gray-400">{{ $ticket->priority }}</td>
                                <td class="px-6 py-4"><x-status-badge :status="$ticket->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No tickets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($tickets, 'links'))
                {{ $tickets->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
