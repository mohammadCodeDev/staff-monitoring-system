<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manual Attendance Entry for') }}: {{ $employee->fullName }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900 dark:text-gray-100">
                    <div class="flex flex-col items-center text-center">
                        {{-- Employee Details --}}
                        <h3 class="text-2xl font-bold dark:text-white">{{ $employee->fullName }}</h3>
                        <p class="text-md text-gray-500 dark:text-gray-400 mt-1">{{ $employee->department?->name }}</p>

                        {{-- Main Form --}}
                        <form action="{{ route('attendances.store') }}" method="POST" class="w-full mt-8">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                            {{-- Datetime Input Field --}}
                            <div>
                                <x-input-label for="timestamp" :value="__('Event Time (Manual Entry)')" />
                                <x-text-input 
                                    id="timestamp" 
                                    name="timestamp" 
                                    type="datetime-local" 
                                    class="mt-1 block w-full" 
                                    required />
                                <x-input-error class="mt-2" :messages="$errors->get('timestamp')" />
                            </div>

                            {{-- Action Buttons (Entry/Exit) --}}
                            <div class="mt-8 flex space-x-4 rtl:space-x-reverse w-full">
                                {{-- Exit Button --}}
                                <button type="submit" name="event_type" value="exit" class="w-1/2 text-center px-6 py-3 bg-red-600 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 active:bg-red-600 disabled:opacity-25 transition">
                                    {{ __('Exit') }}
                                </button>
                                
                                {{-- Entry Button --}}
                                <button type="submit" name="event_type" value="entry" class="w-1/2 text-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-lg text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 active:bg-green-600 disabled:opacity-25 transition">
                                    {{ __('Entry') }}
                                </button>
                            </div>
                        </form>

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
