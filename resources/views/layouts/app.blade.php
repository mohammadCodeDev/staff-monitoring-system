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

    <!-- This script handles theme switching based on localStorage -->
    <script>
        const theme = localStorage.getItem('theme');
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        {{-- This new flex container creates the two-column layout --}}
        <div class="flex">

            <!-- Page Content (Main Area) -->

             <!-- Sidebar -->
            {{-- The sidebar is now placed *before* the main content in the code --}}
            {{-- The sidebar is only included if a user is authenticated --}}
            @auth
            @include('layouts.partials.sidebar')
            @endauth
            
            <main class="flex-1">
                <!-- Page Heading -->
                @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
                @endisset

                <!-- Slot for page-specific content -->
                {{ $slot }}
            </main>
            
        </div>
    </div>
</body>

</html>