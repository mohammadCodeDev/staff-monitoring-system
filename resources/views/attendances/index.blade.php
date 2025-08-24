<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Attendance Monitoring') }}
            </h2>
            {{-- Link to the attendance logging page --}}
            <a href="{{ route('attendances.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 ...">
                {{ __('Log Attendance') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Employee') }}</th>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Event Type') }}</th>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Timestamp') }}</th>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Recorded By (Guard)') }}</th>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($attendances as $attendance)
                                <tr>
                                    <td class="px-6 py-4">{{ $attendance->employee->fullName }}</td>
                                    <td class="px-6 py-4">
                                        @if($attendance->event_type == 'entry')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ __('Entry') }}</span>
                                        @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ __('Exit') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $attendance->timestamp }}</td>
                                    <td class="px-6 py-4">{{ $attendance->recorder->first_name }} {{ $attendance->recorder->last_name }}</td>
                                    {{-- Actions Column with Authorization --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{--
                                            The @can directive checks the 'update' policy for the given attendance record.
                                            We will define this policy in the next step.
                                        --}}
                                        @can('update', $attendance)
                                        <div class="flex justify-start space-x-4 rtl:space-x-reverse">
                                            <a href="{{ route('attendances.edit', $attendance->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">{{ __('Edit') }}</a>

                                            {{-- The delete button is also protected by a policy check --}}
                                            @can('delete', $attendance)
                                            <form action="{{ route('attendances.destroy', $attendance->id) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to delete this record?') }}')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">{{ __('Delete') }}</button>
                                            </form>
                                            @endcan
                                        </div>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    {{-- colspan should be 5 to match the number of columns --}}
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ __('No attendance records found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>