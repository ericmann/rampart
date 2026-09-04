<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($stats as $label => $count)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                        <div class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $label }}</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $count }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200">Recent tickets</h3>
                    <a href="{{ route('tickets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all &rarr;</a>
                </div>
                @if ($recentTickets->isEmpty())
                    <p class="p-6 text-gray-500">No tickets yet.</p>
                @else
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($recentTickets as $ticket)
                            <li>
                                <a href="{{ route('tickets.show', $ticket) }}" class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">#{{ $ticket->id }} &middot; {{ $ticket->subject }}</div>
                                        <div class="text-sm text-gray-500">{{ $ticket->requester->name }} &middot; {{ $ticket->created_at->diffForHumans() }}</div>
                                    </div>
                                    <x-status-badge :status="$ticket->status" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
