<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance Monitoring') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">{{ __('No attendance records found.') }}</td>
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