<x-app-layout>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Employee List') }}
            </h2>
            {{-- Add New Employee Link --}}
            <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                {{ __('Add New Employee') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Auto-hiding Success Message with Alpine.js --}}
            @if (session('success'))
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                role="alert">
                <strong class="font-bold">{{ __('Success!') }}</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            {{-- Search Box --}}
            <div class="mb-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('employees.index') }}" method="GET">
                        <x-input-label for="search" :value="__('Search Employee')" />
                        <div class="flex items-center mt-1">
                            <x-text-input id="search" name="search" type="text" class="block w-full" placeholder="{{ __('Enter name or department...') }}" />
                            <x-primary-button class="ms-3">
                                {{ __('Search') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Employee List Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Full Name') }}</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Department') }}</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($employees as $employee)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $employee->fullName }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ __($employee->department->name) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($employee->is_active)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-800">{{ __('Active') }}</span>
                                        @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-red-800">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{-- The 'justify-start' class handles the alignment automatically for both LTR and RTL --}}
                                        <div class="flex justify-start space-x-4 rtl:space-x-reverse">

                                            {{-- Edit Button --}}
                                            <a href="{{ route('employees.edit', $employee->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">{{ __('Edit') }}</a>

                                            {{-- Conditional Activate/Deactivate Button --}}
                                            @if($employee->is_active)
                                            <form action="{{ route('employees.deactivate', $employee->id) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to deactivate this employee?') }}')) $el.submit()">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-200">{{ __('Deactivate') }}</button>
                                            </form>
                                            @else
                                            <form action="{{ route('employees.reactivate', $employee->id) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to reactivate this employee?') }}')) $el.submit()">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200">{{ __('Reactivate') }}</button>
                                            </form>
                                            @endif

                                            {{-- Delete Button --}}
                                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to permanently delete this employee? This action cannot be undone.') }}')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">{{ __('No employees found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>