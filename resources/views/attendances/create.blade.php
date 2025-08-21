<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Log Attendance') }}
            </h2>
            {{-- Link to the attendance monitoring page --}}
            <a href="{{ route('attendances.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                {{ __('View Attendance Log') }}
            </a>
        </div>
    </x-slot>

    {{--
        This is the main Alpine.js component. 
        - 'view' now reliably controls which part of the UI is visible ('search' or 'confirm').
    --}}
    <div class="py-12" x-data="{
        view: 'search',
        search: '',
        resultsHtml: '',
        selectedEmployee: null,
        isLoading: false,

        fetchEmployees() {
            if (this.search.trim() === '') {
                this.resultsHtml = '';
                return;
            }
            this.isLoading = true;
            fetch(`{{ route('attendances.searchEmployees') }}?search=${this.search}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                this.resultsHtml = html; 
                this.isLoading = false;
            });
        },

        // This is the robust function to handle employee selection
        selectEmployee(employee) {
            if (typeof employee !== 'object' || employee === null) {
                console.error('Invalid employee data received:', employee);
                return;
            }
            
            // Create a clean copy to safely manipulate
            let processedEmployee = JSON.parse(JSON.stringify(employee));

            // Safely parse translatable fields from JSON strings to objects
            if (processedEmployee.department && typeof processedEmployee.department.name === 'string') {
                try {
                    processedEmployee.department.name = JSON.parse(processedEmployee.department.name);
                } catch (e) {
                    processedEmployee.department.name = null;
                }
            }

            if (processedEmployee.group && typeof processedEmployee.group.name === 'string') {
                try {
                    processedEmployee.group.name = JSON.parse(processedEmployee.group.name);
                } catch (e) {
                    processedEmployee.group.name = null;
                }
            }

            this.selectedEmployee = processedEmployee;
            this.view = 'confirm'; // Switch to the confirmation view
        },

        // Resets the UI back to the search state
        resetView() {
            this.view = 'search';
            this.search = '';
            this.resultsHtml = '';
            this.selectedEmployee = null;
        }
    }">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- The success message will now also trigger the resetView function --}}
            @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => { show = false; resetView(); }, 3000)" x-show="show" x-transition
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
            @endif

            {{-- We use <template> with x-if to completely swap the views in the DOM --}}

            {{-- SEARCH VIEW --}}
            <template x-if="view === 'search'">
                <div x-transition>
                    {{-- Search Box --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <x-input-label for="search" :value="__('Search Employee')" />
                            <x-text-input
                                id="search"
                                type="text"
                                class="block w-full mt-1"
                                placeholder="{{ __('Enter full name...') }}"
                                x-model="search"
                                x-on:input.debounce.500ms="fetchEmployees()"
                                autofocus />
                        </div>
                    </div>

                    {{-- Search Results Table --}}
                    <div x-show="search.trim() !== ''" class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-transition>
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Actions') }}</th>
                                            <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Full Name') }}</th>
                                            <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Department') }}</th>
                                            <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Group Name') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" x-html="resultsHtml">
                                        {{-- AJAX results will be injected here --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            <!--
            {{-- CONFIRMATION VIEW --}}
            <template x-if="view === 'confirm'">
                <div x-transition class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 text-gray-900 dark:text-gray-100">
                        <div class="flex flex-col items-center text-center">
                            {{-- Placeholder for employee image --}}
                            <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-full mb-4 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>

                            {{-- Employee Details --}}
                            <h3 class="text-2xl font-bold dark:text-white" x-text="selectedEmployee ? selectedEmployee.first_name + ' ' + selectedEmployee.last_name : ''"></h3>
                            <p class="text-md text-gray-500 dark:text-gray-400 mt-1" x-text="selectedEmployee && selectedEmployee.department && selectedEmployee.department.name ? selectedEmployee.department.name.{{ app()->getLocale() }} : ''"></p>

                            {{-- Action Buttons (Entry/Exit) --}}
                            <div class="mt-8 flex space-x-4 rtl:space-x-reverse w-full">
                                {{-- Exit Form --}}
                                <form action="{{ route('attendances.store') }}" method="POST" class="w-1/2">
                                    @csrf
                                    <input type="hidden" name="event_type" value="exit">
                                    <input type="hidden" name="employee_id" :value="selectedEmployee ? selectedEmployee.id : ''">
                                    <button type="submit" class="w-full text-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 disabled:opacity-25 transition">
                                        {{ __('Exit') }}
                                    </button>
                                </form>

                                {{-- Entry Form --}}
                                <form action="{{ route('attendances.store') }}" method="POST" class="w-1/2">
                                    @csrf
                                    <input type="hidden" name="event_type" value="entry">
                                    <input type="hidden" name="employee_id" :value="selectedEmployee ? selectedEmployee.id : ''">
                                    <button type="submit" class="w-full text-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 active:bg-green-600 disabled:opacity-25 transition">
                                        {{ __('Entry') }}
                                    </button>
                                </form>
                            </div>

                            {{-- Cancel Button --}}
                            <div class="mt-6">
                                <button type="button" @click="resetView()" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
    -->

        </div>
    </div>
</x-app-layout>