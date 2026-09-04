<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Settings</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <dl class="text-sm divide-y divide-gray-100 dark:divide-gray-700">
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Environment</dt>
                        <dd class="font-medium">{{ $appEnv }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Debug mode</dt>
                        <dd class="font-medium">{{ $appDebug ? 'Enabled' : 'Disabled' }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">CORS allowed origins</dt>
                        <dd class="font-medium font-mono">{{ implode(', ', $corsAllowedOrigins ?? []) }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">CORS paths</dt>
                        <dd class="font-medium font-mono">{{ implode(', ', $corsPaths ?? []) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
