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

    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="max-w-xl">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Language Settings') }}
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Select your preferred language for the application.') }}
            </p>

            <form method="POST" action="{{ route('settings.locale.update') }}" class="mt-6">
                @csrf
                <div class="mt-2 space-y-2">
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="locale" value="fa" onchange="this.form.submit()" {{ session('locale', config('app.locale')) == 'fa' ? 'checked' : '' }} class="form-radio text-indigo-600">
                            <span class="ml-2">{{ __('Persian') }}</span>
                        </label>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="radio" name="locale" value="en" onchange="this.form.submit()" {{ session('locale', config('app.locale')) == 'en' ? 'checked' : '' }} class="form-radio text-indigo-600">
                            <span class="ml-2">{{ __('English') }}</span>
                        </label>
                    </div>
                </div>

                @if (session('status') === 'locale-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm mt-2 text-green-600 dark:text-green-400">{{ __('Language updated.') }}</p>
                @endif
            </form>
        </div>
    </div>
    </div>
    </div>

</x-app-layout>