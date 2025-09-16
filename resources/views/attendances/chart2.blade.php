<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance Chart 2') }}
        </h2>
    </x-slot>

    {{-- Alpine.js component for live search --}}
    <div class="py-12" x-data="{
        search: '',
        resultsHtml: '',
        resultsCount: 0, 
        isLoading: false,

        fetchEmployees() {
            if (this.search.trim() === '') {
                this.resultsHtml = '';
                this.resultsCount = 0;
                return;
            }
            this.isLoading = true;
            // IMPORTANT: We add '&view=chart' to the URL here
            fetch(`{{ route('attendances.searchEmployees2') }}?search=${encodeURIComponent(this.search.trim())}&view=chart`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                this.resultsHtml = data.html; 
                this.resultsCount = data.count;
                this.isLoading = false;
            });
        }
    }">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider w-40">{{ __('Actions') }}</th>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider w-20">{{ __('Photo') }}</th>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider w-1/3">{{ __('Full Name') }}</th>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider w-1/3">{{ __('Department') }}</th>
                                    <th class="px-6 py-3 text-left rtl:text-right text-xs font-medium uppercase tracking-wider w-1/3">{{ __('Group Name') }}</th>
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
    </div>
</x-app-layout>