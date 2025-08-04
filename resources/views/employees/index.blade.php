<x-app-layout>
    <x-slot name="header">
        {{-- ... Header content remains the same ... --}}
    </x-slot>

    {{-- We wrap the main content in an Alpine.js component --}}
    <div class="py-12" x-data="{
        search: '',
        employeesHtml: '',
        isLoading: false,
        fetchEmployees() {
            this.isLoading = true;
            fetch(`{{ route('employees.index') }}?search=${this.search}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                this.employeesHtml = html;
                this.isLoading = false;
            });
        },
        init() {
            this.employeesHtml = this.$refs.initialEmployees.innerHTML;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- ... Success Message remains the same ... --}}

            {{-- Search Box --}}
            <div class="mb-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- We removed the <form> tag and added Alpine directives --}}
                    <x-input-label for="search" :value="__('Search Employee')" />
                    <div class="flex items-center mt-1">
                        <x-text-input
                            id="search"
                            name="search"
                            type="text"
                            class="block w-full"
                            placeholder="{{ __('Enter name or department...') }}"
                            x-model="search"
                            x-on:input.debounce.500ms="fetchEmployees()"
                        />
                        {{-- The search button is no longer needed --}}
                    </div>
                </div>
            </div>

            {{-- Employee List Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto relative">
                        {{-- Loading Spinner --}}
                        <div x-show="isLoading" class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center">
                            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-gray-900 dark:border-gray-100"></div>
                        </div>

                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                {{-- ... thead content remains the same ... --}}
                            </thead>
                            {{-- This tbody will be dynamically updated by Alpine.js --}}
                            <tbody x-html="employeesHtml" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {{-- Initial content will be loaded here --}}
                            </tbody>
                        </table>

                        {{-- Template to hold the initial server-rendered content --}}
                        <template x-ref="initialEmployees" class="hidden">
                            @include('employees.partials._employee-rows', ['employees' => $employees])
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>