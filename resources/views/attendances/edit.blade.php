<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Attendance Record') }}
            </h2>
            {{-- Link to go back to the monitoring page --}}
            <a href="{{ route('attendances.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                {{ __('View Attendance Log') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">

                    {{-- Form points to the update route with PUT method --}}
                    <form action="{{ route('attendances.update', $attendance->id) }}" method="POST" class="w-full">
                        @csrf
                        @method('PUT')

                        {{-- Display Employee Name (Read-only) --}}
                        <div class="mb-6">
                            <x-input-label for="employee_name" :value="__('Employee')" />
                            <x-text-input
                                id="employee_name"
                                type="text"
                                class="mt-1 block w-full bg-gray-100 dark:bg-gray-700"
                                :value="$attendance->employee->fullName"
                                disabled />
                        </div>

                        {{-- Datetime Input Field --}}
                        <div>
                            <x-input-label for="timestamp" :value="__('Timestamp')" />
                            <x-text-input
                                id="timestamp"
                                name="timestamp"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                {{-- Pre-fill with existing timestamp, formatted for the input --}}
                                :value="old('timestamp', \Carbon\Carbon::parse($attendance->timestamp)->format('Y-m-d\TH:i'))"
                                required />
                            <x-input-error class="mt-2" :messages="$errors->get('timestamp')" />
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-8 flex items-center justify-end space-x-4 rtl:space-x-reverse">
                            <a href="{{ route('attendances.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>