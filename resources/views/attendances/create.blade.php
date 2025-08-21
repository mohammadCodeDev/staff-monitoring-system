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
        selectedEmployee: null,

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
            <div x-data="{ show: true }" x-init="setTimeout(() => { show = false; resetView(); }, 3000)" x-show="show" x-transition
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('success') }}
            </div>
            @endif

            {{-- We use <template> with x-if to completely swap the views in the DOM --}}

            {{-- SEARCH VIEW --}}

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
        <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div @click.away="isModalOpen = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 w-full max-w-md">
                <h3 class="text-xl font-bold mb-4" x-text="`{{ __('Manual Attendance Entry for') }}: ${manualEntryEmployee ? manualEntryEmployee.fullName : ''}`"></h3>

                <form action="{{ route('attendances.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" :value="manualEntryEmployee ? manualEntryEmployee.id : ''">

                    <div>
                        <x-input-label for="timestamp" :value="__('Event Time (Manual Entry)')" />
                        <x-text-input id="timestamp" name="timestamp" type="datetime-local" class="mt-1 block w-full" required />
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