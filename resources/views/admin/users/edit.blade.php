<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit {{ $user->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" value="{{ old('name', $user->name) }}" required />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $user->email) }}" required />
                    </div>
                    <div>
                        <x-input-label for="role" value="Role" />
                        <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            @foreach (['customer','agent','admin'] as $role)
                                <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="organization_id" value="Organization" />
                        <select id="organization_id" name="organization_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                            <option value="">None</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}" @selected(old('organization_id', $user->organization_id) == $organization->id)>{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>Save</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
