<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">New article</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Preview a link</h3>
                <p class="text-sm text-gray-500 mb-3">Paste a URL to fetch a quick preview before you cite it in the article body.</p>
                <div class="flex gap-3">
                    <input type="text" id="preview-url" placeholder="https://example.com/article"
                           class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm">
                    <button type="button" id="preview-button" class="rounded-md bg-gray-800 text-white text-sm px-4 py-2 hover:bg-gray-700">Preview</button>
                </div>
                <pre id="preview-result" class="mt-3 text-xs bg-gray-50 dark:bg-gray-900 rounded-md p-3 overflow-x-auto hidden"></pre>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('kb.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="title" value="Title" />
                        <x-text-input id="title" name="title" class="mt-1 block w-full" value="{{ old('title') }}" required />
                    </div>
                    <div>
                        <x-input-label for="body" value="Body (HTML allowed)" />
                        <textarea id="body" name="body" rows="10" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm font-mono text-sm" required>{{ old('body') }}</textarea>
                    </div>
                    <x-primary-button>Publish</x-primary-button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('preview-button').addEventListener('click', async () => {
            const url = document.getElementById('preview-url').value;
            const result = document.getElementById('preview-result');
            result.classList.remove('hidden');
            result.textContent = 'Loading…';

            const response = await fetch('{{ route('kb.preview-link') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ url }),
            });
            const data = await response.json();
            result.textContent = data.preview ?? JSON.stringify(data);
        });
    </script>
</x-app-layout>
