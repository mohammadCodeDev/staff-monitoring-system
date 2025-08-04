<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{-- Using getTranslation to show a specific language in the title --}}
            {{ __('Edit Department') . ': ' . $department->getTranslation('name', app()->getLocale()) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('departments.update', $department) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- English Name --}}
                        <div class="mt-4">
                            <x-input-label for="name_en" :value="__('Department Name') . ' (English)'" />
                            {{-- Use getTranslation to pre-fill the value for English --}}
                            <x-text-input id="name_en" name="name[en]" type="text" class="mt-1 block w-full" :value="old('name.en', $department->getTranslation('name', 'en'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name.en')" />
                        </div>

                        {{-- Persian Name --}}
                        <div class="mt-4">
                            <x-input-label for="name_fa" :value="__('Department Name') . ' (فارسی)'" />
                            {{-- Use getTranslation to pre-fill the value for Persian --}}
                            <x-text-input id="name_fa" name="name[fa]" type="text" class="mt-1 block w-full" :value="old('name.fa', $department->getTranslation('name', 'fa'))" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name.fa')" />
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