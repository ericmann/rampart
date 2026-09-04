<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} &mdash; Support desk that scales with your team</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <header class="border-b border-gray-100 dark:border-gray-800">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-application-logo class="h-8 w-auto text-indigo-600" />
                <span class="font-bold text-lg">Rampart</span>
            </div>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('kb.index') }}" class="hover:text-indigo-600">Knowledge Base</a>
                <a href="{{ route('login') }}" class="hover:text-indigo-600">Log in</a>
                <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 text-white px-4 py-2 hover:bg-indigo-500">Get started</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="max-w-6xl mx-auto px-6 py-24 text-center">
            <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">Support tickets, without the chaos.</h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Rampart gives your team one queue, one knowledge base, and one place to see
                what your customers are actually asking for.
            </p>
            <div class="mt-8 flex items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 text-white px-6 py-3 font-medium hover:bg-indigo-500">Start free</a>
                <a href="{{ route('kb.index') }}" class="rounded-md border border-gray-300 dark:border-gray-700 px-6 py-3 font-medium hover:bg-gray-50 dark:hover:bg-gray-800">Browse the docs</a>
            </div>
        </section>

        <section class="max-w-6xl mx-auto px-6 pb-24 grid sm:grid-cols-3 gap-8">
            <div>
                <h3 class="font-semibold">Shared inboxes</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Every ticket lands in one queue your whole team can see and search.</p>
            </div>
            <div>
                <h3 class="font-semibold">Knowledge base</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Publish answers once, deflect the repeat questions forever.</p>
            </div>
            <div>
                <h3 class="font-semibold">Integrations</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Webhooks and API tokens so Rampart plugs into the rest of your stack.</p>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-100 dark:border-gray-800 py-8 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Rampart, Inc.
    </footer>
</body>
</html>
