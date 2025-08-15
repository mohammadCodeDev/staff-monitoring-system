{{--
    This partial view renders the rows for the live employee search results.
    It's designed to be fetched via AJAX.
--}}

@forelse ($employees as $employee)
<tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
    {{-- Select Button Column --}}
    <td class="px-6 py-4 whitespace-nowrap">
        {{--
                This button is the key. When clicked, it calls the 'selectEmployee' function in Alpine.js.
                We pass the entire employee object as a JSON string to the function.
                The @json blade directive safely converts the PHP object to a JSON string.
            --}}
        <a href="{{ route('attendances.confirm', $employee->id) }}"
            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-200 active:bg-indigo-600 disabled:opacity-25 transition">
            {{ __('Select') }}
        </a>
    </td>

    {{-- Full Name Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
        {{ $employee->fullName }}
    </td>

    {{-- Department Name Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{-- We use optional chaining (?->) to prevent errors if an employee has no department --}}
        {{ $employee->department?->name }}
    </td>

    {{-- Group Name Column --}}
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