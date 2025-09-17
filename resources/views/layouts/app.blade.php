<!DOCTYPE html>
<!-- Add the dir attribute to dynamically set text direction -->
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'fa' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'staff-monitoring-system') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- This script handles Theme and Font size switching based on localStorage -->
    <script>
        // --- THEME LOGIC ---
        // Get user's theme from the database (injected by Blade)
        const userTheme = "{{ Auth::user()->theme ?? 'system' }}";

        // Get theme from localStorage as a fallback or for 'system' mode
        const storedTheme = localStorage.getItem('theme');

        // Determine the theme to apply
        let themeToApply = userTheme;
        if (userTheme === 'system') {
            themeToApply = storedTheme || 'system';
        }

        // Apply the theme
        if (themeToApply === 'dark' || (themeToApply === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark'); // Sync localStorage
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light'); // Sync localStorage
        }

        // --- FONT SIZE LOGIC (NEW) ---
        // Use Blade to inject the font size from the database into a JS variable
        const userFontSize = "{{ Auth::user()->font_size ?? 100 }}";
        // Use pure JavaScript to apply the style to the root element
        document.documentElement.style.fontSize = userFontSize + '%';
    </script>

</head>

<body class="font-sans antialiased">
    {{-- Alpine.js state for sidebar toggle --}}
    <div x-data="{ sidebarOpen: true }" class="h-screen flex flex-col bg-gray-100 dark:bg-gray-900">

        {{-- The header is now a direct child of the flex-col container --}}
        @include('layouts.navigation')

        {{-- This container holds both sidebar and main content, and fills the remaining vertical space --}}
        <div class="flex-1 flex overflow-hidden">

            <!-- Page Content (Main Area) -->

            <!-- Sidebar -->
            {{-- The sidebar is now placed *before* the main content in the code --}}
            {{-- The sidebar is only included if a user is authenticated --}}
            @auth
            @include('layouts.partials.sidebar')
            @endauth

            {{-- Added overflow-y-auto for independent scrolling --}}
            <main class="flex-1 overflow-y-auto">
                <!-- Page Heading -->
                @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
                @endisset

                <div class="p-6"> {{-- Added padding around content --}}
                    <!-- Slot for page-specific content -->
                    {{ $slot }}
                </div>
            </main>

        </div>
    </div>

    {{-- Add the line below right before the closing body tag --}}
    @stack('scripts')

</body>

</html>