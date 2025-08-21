<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{-- Using getTranslation to show a specific language in the title --}}
                {{ __('Edit Department') . ': ' . $department->getTranslation('name', app()->getLocale()) }}
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
                    <form action="{{ route('departments.update', $department) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Persian Name --}}
                        <div class="mt-4">
                            <x-input-label for="name_fa" :value="__('Department Name') . ' (فارسی)'" />
                            {{-- Use getTranslation to pre-fill the value for Persian --}}
                            <x-text-input id="name_fa" name="name[fa]" type="text" class="mt-1 block w-full" :value="old('name.fa', $department->getTranslation('name', 'fa'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name.fa')" />
                        </div>

                        {{-- English Name --}}
                        <div class="mt-4">
                            <x-input-label for="name_en" :value="__('Department Name') . ' (English)'" />
                            {{-- Use getTranslation to pre-fill the value for English --}}
                            <x-text-input id="name_en" name="name[en]" type="text" class="mt-1 block w-full" :value="old('name.en', $department->getTranslation('name', 'en'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name.en')" />
                        </div>

                        {{-- Manager Selection Dropdown --}}
                        <div class="mt-4">
                            <x-input-label for="manager_id" :value="__('Department Head')" />
                            <select name="manager_id" id="manager_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">{{ __('Select a Manager') }}</option>
                                @if(isset($managers))
                                @foreach($managers as $manager)
                                <option value="{{ $manager->id }}" @selected(old('manager_id', $department->manager_id ?? null) == $manager->id)>
                                    {{ $manager->first_name }} {{ $manager->last_name }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <x-primary-button>{{ __('Update Department') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>