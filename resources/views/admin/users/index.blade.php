<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage User Roles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('User') }}</th>
                                    <th class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Current Role') }}</th>
                                    <th class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Change Role') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->userName }})</td>
                                    <td class="px-6 py-4">{{ __($user->role->role_name) }}</td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center">
                                                <select name="role_id" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 ... rounded-md shadow-sm">
                                                    @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" @selected($user->role_id == $role->id)>
                                                        {{ __($role->role_name) }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <x-primary-button class="ms-3">{{ __('Update Role') }}</x-primary-button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>