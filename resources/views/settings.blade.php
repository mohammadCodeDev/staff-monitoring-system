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

                    {{-- This is now a real form that POSTs to the server --}}
                    <form method="POST" action="{{ route('settings.theme.update') }}" class="mt-6">
                        @csrf
                        <div class="mt-2 space-y-2">
                            {{-- Light Theme --}}
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="theme" value="light" onchange="this.form.submit()" 
                                           {{ Auth::user()->theme == 'light' ? 'checked' : '' }} 
                                           class="form-radio text-indigo-600">
                                    <span class="ml-2">{{ __('Light') }}</span>
                                </label>
                            </div>
                            {{-- Dark Theme --}}
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="theme" value="dark" onchange="this.form.submit()" 
                                           {{ Auth::user()->theme == 'dark' ? 'checked' : '' }} 
                                           class="form-radio text-indigo-600">
                                    <span class="ml-2">{{ __('Dark') }}</span>
                                </label>
                            </div>
                            {{-- System Theme --}}
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="theme" value="system" onchange="this.form.submit()" 
                                           {{ Auth::user()->theme == 'system' ? 'checked' : '' }} 
                                           class="form-radio text-indigo-600">
                                    <span class="ml-2">{{ __('System') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Success Message --}}
                        @if (session('status') === 'theme-updated')
                        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm mt-2 text-green-600 dark:text-green-400">{{ __('Theme updated.') }}</p>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
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
                                    <input type="radio" name="locale" value="fa" onchange="this.form.submit()" {{ Auth::user()->locale == 'fa' ? 'checked' : '' }} class="form-radio text-indigo-600">
                                    <span class="ml-2">{{ __('Persian') }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="locale" value="en" onchange="this.form.submit()" {{ Auth::user()->locale == 'en' ? 'checked' : '' }} class="form-radio text-indigo-600">
                                    <span class="ml-2">{{ __('English') }}</span>
                                </label>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="locale" value="system" onchange="this.form.submit()" {{ Auth::user()->locale == 'system' ? 'checked' : '' }} class="form-radio text-indigo-600">
                                    <span class="ml-2">{{ __('System') }}</span>
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