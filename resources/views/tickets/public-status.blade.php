<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket #{{ $ticket->id }} status &mdash; {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <div class="text-sm text-gray-500">Ticket #{{ $ticket->id }}</div>
        <h1 class="text-xl font-semibold mt-1">{{ $ticket->subject }}</h1>
        <div class="mt-4"><x-status-badge :status="$ticket->status" /></div>
        <p class="mt-4 text-sm text-gray-500">This is a read-only status link — no login required. It expires automatically.</p>
    </div>
</body>
</html>
