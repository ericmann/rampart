<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Application Error</title>
    <style>
        body { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; background: #1a1a1a; color: #e5e5e5; margin: 0; padding: 2rem; }
        h1 { color: #f87171; font-size: 1.1rem; }
        .box { background: #111; border: 1px solid #333; border-radius: 8px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; overflow-x: auto; }
        .label { color: #9ca3af; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: 0.25rem 0.75rem 0.25rem 0; vertical-align: top; }
        td.key { color: #93c5fd; white-space: nowrap; }
        pre { white-space: pre-wrap; word-break: break-word; margin: 0; }
    </style>
</head>
<body>
    <h1>{{ get_class($exception) }}: {{ $exception->getMessage() }}</h1>

    <div class="box">
        <div class="label">Thrown in</div>
        <pre>{{ $exception->getFile() }}:{{ $exception->getLine() }}</pre>
    </div>

    <div class="box">
        <div class="label">Stack trace</div>
        <pre>{{ $exception->getTraceAsString() }}</pre>
    </div>

    {{-- Convenience for local debugging — but nothing here checks APP_ENV, so it renders
         wherever APP_DEBUG=true, including the workshop's "prod-ish" compose. --}}
    <div class="box">
        <div class="label">Environment ({{ config('app.env') }})</div>
        <table>
            <tr><td class="key">APP_KEY</td><td>{{ config('app.key') }}</td></tr>
            <tr><td class="key">APP_URL</td><td>{{ config('app.url') }}</td></tr>
            <tr><td class="key">DB_CONNECTION</td><td>{{ config('database.default') }}</td></tr>
            <tr><td class="key">DB_HOST</td><td>{{ config('database.connections.mysql.host') }}</td></tr>
            <tr><td class="key">DB_DATABASE</td><td>{{ config('database.connections.mysql.database') }}</td></tr>
            <tr><td class="key">DB_USERNAME</td><td>{{ config('database.connections.mysql.username') }}</td></tr>
            <tr><td class="key">DB_PASSWORD</td><td>{{ config('database.connections.mysql.password') }}</td></tr>
            <tr><td class="key">REDIS_HOST</td><td>{{ config('database.redis.default.host') }}</td></tr>
        </table>
    </div>
</body>
</html>
