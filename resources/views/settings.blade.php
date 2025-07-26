<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium">{{ __('Theme Settings') }}</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Choose how the application looks. "System" will match your operating system preference.') }}
                    </p>
                    
                    <div x-data="themeSwitcher" class="mt-6 space-y-2">
                        <div>
                            <label class="inline-flex items-center">
                                <input type="radio" name="theme" value="light" @click="setTheme('light')" :checked="theme === 'light'" class="form-radio text-indigo-600">
                                <span class="ml-2">{{ __('Light') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="radio" name="theme" value="dark" @click="setTheme('dark')" :checked="theme === 'dark'" class="form-radio text-indigo-600">
                                <span class="ml-2">{{ __('Dark') }}</span>
                            </label>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="radio" name="theme" value="system" @click="setTheme('system')" :checked="theme === 'system'" class="form-radio text-indigo-600">
                                <span class="ml-2">{{ __('System') }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>