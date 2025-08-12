<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Create Department') }}
            </h2>

            <a href="{{ route('departments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                {{ __('Manage Departments') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('departments.store') }}" method="POST">
                        @csrf
                        {{-- English Name --}}
                        <div class="mt-4">
                            <x-input-label for="name_en" :value="__('Department Name') . ' (English)'" />
                            <x-text-input id="name_en" name="name[en]" type="text" class="mt-1 block w-full" :value="old('name.en')" required />
                        </div>

                        {{-- Persian Name --}}
                        <div class="mt-4">
                            <x-input-label for="name_fa" :value="__('Department Name') . ' (فارسی)'" />
                            <x-text-input id="name_fa" name="name[fa]" type="text" class="mt-1 block w-full" :value="old('name.fa')" required />
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <x-primary-button>{{ __('Add Department') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>