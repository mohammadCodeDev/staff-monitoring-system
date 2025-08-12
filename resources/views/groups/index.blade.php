<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Manage Groups') }}
            </h2>
            <a href="{{ route('groups.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                {{ __('Add Group') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                role="alert">
                {{ session('success') }}
            </div>
            @endif

            {{-- Error Messages --}}
            @if ($errors->any())
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Group Name') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Roles.Group Manager') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Department') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @foreach ($groups as $group)
                            <tr>
                                {{-- Get the translated name based on current language --}}
                                <td class="px-6 py-4">{{ $group->getTranslation('name', app()->getLocale()) }}</td>
                                <td class="px-6 py-4">
                                    <!-- Using optional chaining `?->` to prevent errors if a group has no manager -->
                                    {{ $group->manager?->first_name }} {{ $group->manager?->last_name }}
                                </td>
                                {{-- Display the department name --}}
                                <td class="px-6 py-4">
                                    {{ $group->department->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-start space-x-4 rtl:space-x-reverse">
                                        <a href="{{ route('groups.edit', $group) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">{{ __('Edit') }}</a>

                                        {{-- The form now uses Alpine.js for confirmation to avoid IDE errors --}}
                                        <form action="{{ route('groups.destroy', $group) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to delete this group?') }}')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>