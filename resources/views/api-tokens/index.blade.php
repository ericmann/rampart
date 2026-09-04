<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">API Tokens</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('newToken'))
                <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-lg p-4 text-sm">
                    <p class="font-medium text-green-800 dark:text-green-200">Copy this token now — it's shown once.</p>
                    <code class="block mt-2 break-all bg-white dark:bg-gray-900 rounded p-2">{{ session('newToken') }}</code>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('api-tokens.store') }}" class="flex gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <x-input-label for="name" value="Token name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" placeholder="e.g. Zapier integration" required />
                    </div>
                    <x-primary-button>Create token</x-primary-button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($tokens as $token)
                    <div class="p-4 flex items-center justify-between text-sm">
                        <div>
                            <div class="font-medium">{{ $token->name }}</div>
                            <div class="text-gray-500">Created {{ $token->created_at->diffForHumans() }}</div>
                        </div>
                        <form method="POST" action="{{ route('api-tokens.destroy', $token) }}">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:text-red-500">Revoke</button>
                        </form>
                    </div>
                @empty
                    <p class="p-6 text-gray-500">No API tokens yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
