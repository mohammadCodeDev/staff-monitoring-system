{{--
    This partial view renders the rows for the live employee search results.
    It's designed to be fetched via AJAX.
--}}

@forelse ($employees as $employee)
<tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
    <td class="px-6 py-4 whitespace-nowrap">
        {{-- Action buttons are now in a flex container --}}
        <div class="flex items-center space-x-2 rtl:space-x-reverse">
            {{-- Select button for automatic (current time) entry --}}
            <a href="{{ route('attendances.confirm', $employee->id) }}"
                class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
                {{ __('Select') }}
            </a>

            {{-- Link to the new manual entry page --}}
            <a href="{{ route('attendances.manual-entry', $employee->id) }}"
                class="inline-flex items-center px-3 py-1.5 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                {{ __('Manual Entry') }}
            </a>
        </div>
    </td>
    {{-- Other columns (fullName, department, group) remain the same --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
        {{ $employee->fullName }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->department?->name }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->group?->name }}
    </td>
</tr>
@empty
{{-- This row is shown if the search returns no results --}}
<tr>
    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
        {{ __('No employees found.') }}
    </td>
</tr>
@endforelse