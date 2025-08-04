<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Log Attendance') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('attendances.store') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- Employee Selection --}}
                        <div>
                            <x-input-label for="employee_id" :value="__('Select Employee')" />
                            <select id="employee_id" name="employee_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 ... rounded-md shadow-sm" required>
                                <option value="">---</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                        {{ $employee->fullName }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
                        </div>

                        {{-- Event Type Selection --}}
                        <div>
                            <x-input-label for="event_type" :value="__('Event Type')" />
                            <select id="event_type" name="event_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 ... rounded-md shadow-sm" required>
                                <option value="entry" @selected(old('event_type') == 'entry')>{{ __('Entry') }}</option>
                                <option value="exit" @selected(old('event_type') == 'exit')>{{ __('Exit') }}</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('event_type')" />
                        </div>

                        {{-- Timestamp Input --}}
                        <div>
                            <x-input-label for="timestamp" :value="__('Event Time (Manual Entry)')" />
                            <x-text-input id="timestamp" name="timestamp" type="datetime-local" class="mt-1 block w-full" :value="old('timestamp')"
                                {{-- The timestamp is REQUIRED for admins, optional for guards --}}
                                @if(Auth::user()->role->role_name == 'Roles.System Admin') required @endif
                            />
                            {{-- For Guards, show a helper text --}}
                            @if(Auth::user()->role->role_name == 'Roles.Guard')
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Leave blank for current time') }}</p>
                            @endif
                            <x-input-error class="mt-2" :messages="$errors->get('timestamp')" />
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Log Event') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>