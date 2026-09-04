<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Webhooks</h2>
            <a href="{{ route('admin.webhooks.create') }}" class="rounded-md bg-indigo-600 text-white text-sm px-4 py-2 hover:bg-indigo-500">New webhook</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg p-3 text-sm text-green-800 dark:text-green-200">{{ session('status') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($webhooks as $webhook)
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium">{{ $webhook->name }}</div>
                                <div class="text-sm text-gray-500">{{ $webhook->target_url }} &middot; {{ $webhook->event }}</div>
                                <div class="text-xs text-gray-400 mt-1">Inbound URL: {{ url('/webhooks/inbound/'.$webhook->inbound_token) }}</div>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <form method="POST" action="{{ route('admin.webhooks.test', $webhook) }}">
                                    @csrf
                                    <button class="text-indigo-600 hover:text-indigo-500">Test</button>
                                </form>
                                <a href="{{ route('admin.webhooks.edit', $webhook) }}" class="text-gray-600 hover:text-gray-500">Edit</a>
                                <form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-500">Delete</button>
                                </form>
                            </div>
                        </div>
                        @if ($webhook->deliveries->isNotEmpty())
                            <div class="mt-3 text-xs text-gray-500 space-y-1">
                                @foreach ($webhook->deliveries()->latest()->take(3)->get() as $delivery)
                                    <div>{{ $delivery->direction }} &middot; {{ $delivery->result }} &middot; {{ $delivery->created_at->diffForHumans() }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="p-6 text-gray-500">No webhooks configured.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
