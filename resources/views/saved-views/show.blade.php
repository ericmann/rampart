<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $savedView->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Filter preferences</h3>
                <dl class="text-sm space-y-1">
                    @forelse ($preferences as $key => $value)
                        <div class="flex gap-2">
                            <dt class="text-gray-500">{{ $key }}:</dt>
                            <dd>{{ $value ?? '—' }}</dd>
                        </div>
                    @empty
                        <p class="text-gray-500">No preferences stored.</p>
                    @endforelse
                </dl>
                <a href="{{ route('tickets.index', $preferences) }}" class="inline-block mt-4 text-indigo-600 hover:text-indigo-500 text-sm">Apply to ticket list &rarr;</a>
            </div>
        </div>
    </div>
</x-app-layout>
