<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Knowledge Base</h2>
            @auth
                @if (auth()->user()->isStaff())
                    <a href="{{ route('kb.create') }}" class="rounded-md bg-indigo-600 text-white text-sm px-4 py-2 hover:bg-indigo-500">New article</a>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 grid sm:grid-cols-2 gap-4">
            @forelse ($articles as $article)
                <a href="{{ route('kb.show', $article) }}" class="block bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 hover:shadow-md transition">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $article->title }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ Str::limit(strip_tags($article->body), 140) }}</p>
                </a>
            @empty
                <p class="text-gray-500">No articles published yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
