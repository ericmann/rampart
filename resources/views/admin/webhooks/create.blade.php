<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New webhook</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('admin.webhooks.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="target_url" value="Target URL" />
                        <x-text-input id="target_url" name="target_url" class="mt-1 block w-full" placeholder="https://example.com/hooks/rampart" required />
                    </div>
                    <div>
                        <x-input-label for="event" value="Event" />
                        <select id="event" name="event" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            <option value="ticket.created">ticket.created</option>
                            <option value="ticket.closed">ticket.closed</option>
                        </select>
                    </div>
                    <x-primary-button>Create webhook</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
