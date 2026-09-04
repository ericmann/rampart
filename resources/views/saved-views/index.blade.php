<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Saved Views</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Save a filter</h3>
                <form method="POST" action="{{ route('saved-views.store') }}" class="grid sm:grid-cols-4 gap-3 items-end">
                    @csrf
                    <div class="sm:col-span-2">
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                            <option value="">Any</option>
                            @foreach (['open','pending','resolved','closed'] as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Save view</x-primary-button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
                @forelse ($views as $view)
                    <div class="p-4 flex items-center justify-between">
                        <a href="{{ route('saved-views.show', $view) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $view->name }}</a>
                        <form method="POST" action="{{ route('saved-views.destroy', $view) }}">
                            @csrf @method('DELETE')
                            <button class="text-sm text-red-600 hover:text-red-500">Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="p-6 text-gray-500">No saved views yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
