<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Confirm Attendance for') }}: {{ $employee->fullName }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col items-center text-center">
                        {{-- Placeholder for employee image --}}
                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-full mb-4 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>

                        {{-- Employee Details --}}
                        <h3 class="text-2xl font-bold dark:text-white">{{ $employee->fullName }}</h3>
                        <p class="text-md text-gray-500 dark:text-gray-400 mt-1">{{ $employee->department?->name }}</p>

                        {{-- Action Buttons (Entry/Exit) --}}
                        <div class="mt-8 flex space-x-4 rtl:space-x-reverse w-full">
                            {{-- Exit Form --}}
                            <form action="{{ route('attendances.store') }}" method="POST" class="w-1/2">
                                @csrf
                                <input type="hidden" name="event_type" value="exit">
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <button type="submit" class="w-full text-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 disabled:opacity-25 transition">
                                    {{ __('Exit') }}
                                </button>
                            </form>

                            {{-- Entry Form --}}
                            <form action="{{ route('attendances.store') }}" method="POST" class="w-1/2">
                                @csrf
                                <input type="hidden" name="event_type" value="entry">
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                <button type="submit" class="w-full text-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 active:bg-green-600 disabled:opacity-25 transition">
                                    {{ __('Entry') }}
                                </button>
                            </form>
                        </div>

                        {{-- Cancel Button --}}
                        <div class="mt-6">
                            <a href="{{ route('attendances.create') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>