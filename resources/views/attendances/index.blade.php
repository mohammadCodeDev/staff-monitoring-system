<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Attendance Monitoring') }}
            </h2>
            @can('create', App\Models\Attendance::class) {{-- Added authorization check --}}
            <a href="{{ route('attendances.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                {{ __('Log Attendance') }}
            </a>
            @endcan
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
                                    {{-- ستون‌های جدید --}}
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Employee') }}</th>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Entry Time') }}</th>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Exit Time') }}</th>
                                    <th scope="col" class="px-6 py-3 {{ app()->getLocale() == 'fa' ? 'text-right' : 'text-left' }}">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($attendances as $attendance_pair)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $attendance_pair->employee->fullName ?? __('N/A') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $attendance_pair->entry_time ? \Carbon\Carbon::parse($attendance_pair->entry_time)->format('H:i:s') : '---' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $attendance_pair->exit_time ? \Carbon\Carbon::parse($attendance_pair->exit_time)->format('H:i:s') : '---' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($attendance_pair->attendance_date)->format('Y-m-d') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">{{ __('No attendance records found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- افزودن لینک‌های صفحه‌بندی --}}
                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>