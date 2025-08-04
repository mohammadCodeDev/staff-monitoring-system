<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }} - {{ Auth::user()->userName }} ({{ __(Auth::user()->role->role_name) }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}

                    <!-- Link to the welcome page -->
                    <div class="mt-4">
                        <a href="{{ route('welcome') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Back to Welcome Page') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Actions Section -->
    <!-- This section is only visible to users with the 'System Admin' role -->
    @if(Auth::user()->role->role_name == 'Roles.System Admin')
    <div class="py-6 pt-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold text-lg mb-4">{{ __('Admin Actions') }}</h3>
                    <div class="flex space-x-4">

                        <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                            {{ __('Add New Employee') }}
                        </a>

                        <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                            {{ __('View All Employees') }}
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 ...">
                            {{ __('Manage User Roles') }}
                        </a>

                        <a href="{{ route('attendances.index') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 border ...">
                            {{ __('Attendance Monitoring') }}
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Other Roles' Action Section -->
    @php
    $monitoringRoles = ['Roles.System Observer', 'Roles.University President', 'Roles.Faculty Head', 'Roles.Group Manager'];
    @endphp

    @if(in_array(Auth::user()->role->role_name, $monitoringRoles))
    <div class="py-6 pt-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold text-lg mb-4">{{ __('Monitoring Actions') }}</h3>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('attendances.index') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 border ...">
                            {{ __('View Attendance Log') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!--
    <div class="py-12 pt-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="{{ route('settings') }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        {{ __('Go to Settings') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
-->

</x-app-layout>