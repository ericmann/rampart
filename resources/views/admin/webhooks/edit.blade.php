<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit webhook</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('admin.webhooks.update', $webhook) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $webhook->name) }}" required />
                    </div>
                    <div>
                        <x-input-label for="target_url" value="Target URL" />
                        <x-text-input id="target_url" name="target_url" class="mt-1 block w-full" value="{{ old('target_url', $webhook->target_url) }}" required />
                    </div>
                    <div>
                        <x-input-label for="event" value="Event" />
                        <select id="event" name="event" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            @foreach (['ticket.created','ticket.closed'] as $event)
                                <option value="{{ $event }}" @selected(old('event', $webhook->event) === $event)>{{ $event }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $webhook->is_active)) class="rounded border-gray-300">
                        Active
                    </label>
                    <x-primary-button>Save</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
