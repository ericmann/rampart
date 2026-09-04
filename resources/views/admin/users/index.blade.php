<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Users</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Organization</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($users as $user)
                            <tr>
                                <td class="px-6 py-3">{{ $user->name }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-3 capitalize">{{ $user->role }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ $user->organization->name ?? '—' }}</td>
                                <td class="px-6 py-3 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-500">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
