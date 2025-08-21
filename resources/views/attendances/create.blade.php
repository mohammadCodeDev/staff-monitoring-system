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
        search: '',
        resultsHtml: '',
        resultsCount: 0, 
        isLoading: false,
        isModalOpen: false,
        manualEntryEmployee: null,

        fetchEmployees() {
            if (this.search.trim() === '') {
                this.resultsHtml = '';
                this.resultsCount = 0;
                return;
            }
            this.isLoading = true;
            fetch(`{{ route('attendances.searchEmployees') }}?search=${encodeURIComponent(this.search.trim())}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                this.resultsHtml = data.html; 
                this.resultsCount = data.count;
                this.isLoading = false;
            });
        },

        openModal(employee) {
            this.manualEntryEmployee = employee;
            this.isModalOpen = true;
        }

        
    }" @open-manual-entry-modal.window="openModal($event.detail.employee)">

        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- The success message will now also trigger the resetView function --}}
            @if (session('success'))
            <div x-data="{ show: true }"
                x-init="setTimeout(() => { 
                    show = false;      // Hide the message
                    search = '';       // Clear the search input
                    fetchEmployees();  // Clear the results list
                }, 3000)"
                x-show="show"
                x-transition
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
            @endif

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
                <div class="p-6 pb-16 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Actions') }}</th>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Full Name') }}</th>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Department') }}</th>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider">{{ __('Group Name') }}</th>
                                    <th class="px-6 py-3 w-12"></th> {{-- Empty header for the kebab menu --}}
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" x-html="resultsHtml">
                                {{-- AJAX results will be injected here --}}
                            </tbody>
                        </table>
                    </div>

                    <div x-show="resultsCount >= 10" class="mt-4 text-sm text-center text-gray-500 dark:text-gray-400">
                        {{ __('More results may be available. Please refine your search.') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- MANUAL ENTRY MODAL --}}
        <div x-show="isModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div @click.away="isModalOpen = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100" x-text="`{{ __('Manual Attendance Entry for') }}: ${manualEntryEmployee ? manualEntryEmployee.first_name + ' ' + manualEntryEmployee.last_name : ''}`"></h3>

                <form action="{{ route('attendances.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" :value="manualEntryEmployee ? manualEntryEmployee.id : ''">

                    <div>
                        <x-input-label for="timestamp" :value="__('Event Time (Manual Entry)')" />
                        <x-text-input id="timestamp" name="timestamp" type="datetime-local" class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-300 dark:invert" required />
                    </div>

                    <div class="mt-8 flex space-x-4 rtl:space-x-reverse w-full">
                        <button type="submit" name="event_type" value="entry" class="w-1/2 text-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none transition">
                            {{ __('Entry') }}
                        </button>
                        <button type="submit" name="event_type" value="exit" class="w-1/2 text-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none transition">
                            {{ __('Exit') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>