<x-app-layout>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Employee List') }}
            </h2>
            {{-- Add New Employee Link --}}
            <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                {{ __('Add New Employee') }}
            </a>
        </div>
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

            {{-- Auto-hiding Success Message with Alpine.js --}}
            @if (session('success'))
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                role="alert">
                <strong class="font-bold">{{ __('Success!') }}</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

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
                            x-on:input.debounce.500ms="fetchEmployees()" />
                        {{-- The search button is no longer needed --}}
                    </div>
                </div>
            </div>

            {{-- Employee List Table --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Full Name') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Department') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Group Name') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left rtl:text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" x-html="employeesHtml">
                                {{-- The employee rows will be dynamically injected here by Alpine.js --}}
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