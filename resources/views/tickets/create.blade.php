<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New ticket</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('tickets.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="subject" value="Subject" />
                        <x-text-input id="subject" name="subject" class="mt-1 block w-full" value="{{ old('subject') }}" required />
                        <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="priority" value="Priority" />
                        <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm">
                            @foreach (['low','normal','high','urgent'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority') === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="body" value="Describe the issue" />
                        <textarea id="body" name="body" rows="6" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm" required>{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>
                    <x-primary-button>Submit ticket</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
