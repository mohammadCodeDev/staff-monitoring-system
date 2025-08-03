<x-app-layout>
    {{-- This slot defines the header content, which is the page title. --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Employee/Professor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- The form for creating a new employee, styled with Tailwind CSS classes. --}}
                    <form action="{{ route('employees.store') }}" method="POST" class="space-y-6">
                        @csrf {{-- CSRF protection token --}}

                        {{-- First Name Input --}}
                        <div>
                            <x-input-label for="first_name" :value="__('First Name')" />
                            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                        </div>

                        {{-- Last Name Input --}}
                        <div>
                            <x-input-label for="last_name" :value="__('Last Name')" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                        </div>

                        {{-- Department Selection --}}
                        <div>
                            <x-input-label for="department_id" :value="__('Department')" />
                            <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">{{ __('Select a Department') }}</option>
                                @foreach ($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save Employee') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>