@forelse($employees as $employee)
<tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
    {{-- Actions Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2 rtl:space-x-reverse">
            <a href="{{ route('employees.reports.monthly', $employee->id) }}" class="px-2 py-1 border border-indigo-600 text-indigo-600 rounded-md text-xs hover:bg-indigo-600 hover:text-white transition-colors duration-200">
                {{ __('Monthly Report') }}
            </a>
            <a href="{{ route('employees.reports.yearly', $employee->id) }}" class="px-2 py-1 border border-teal-600 text-teal-600 rounded-md text-xs hover:bg-teal-600 hover:text-white transition-colors duration-200">
                {{ __('Yearly Report') }}
            </a>
        </div>
    </td>

    {{-- Photo Column (CORRECTED) --}}
    <td class="px-6 py-4 whitespace-nowrap">
        {{-- Logic now matches the main employees list for consistency --}}
        @if ($employee->photo_path)
        <img src="{{ $employee->profilePhotoUrl }}"
            alt="{{ $employee->full_name }}"
            class="h-10 w-10 rounded-full object-cover transition-transform duration-300 transform hover:scale-150">
        @else
        <div class="h-10 w-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-lg transition-transform duration-300 transform hover:scale-150">
            {{ mb_substr($employee->first_name, 0, 1) }}
        </div>
        @endif
    </td>

    {{-- Full Name Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
        {{ $employee->full_name }}
    </td>

    {{-- Department Column (Using nullsafe operator for consistency) --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->department?->name ?? __('N/A') }}
    </td>

    {{-- Group Column (Using nullsafe operator for consistency) --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->group?->name ?? __('N/A') }}
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
        {{ __('No employees found.') }}
    </td>
</tr>
@endforelse