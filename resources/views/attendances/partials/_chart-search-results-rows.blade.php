@forelse($employees as $employee)
<tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
    {{-- Actions Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2 rtl:space-x-reverse">
            {{-- NOTE: These routes do not exist yet. We will create them later. --}}
            <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">{{ __('Monthly Report') }}</a>
            <a href="#" class="text-teal-600 hover:text-teal-900 dark:text-teal-400 dark:hover:text-teal-200">{{ __('Yearly Report') }}</a>
        </div>
    </td>

    {{-- Photo Column --}}
    <td class="px-6 py-4 whitespace-nowrap">
        @if ($employee->photo_path)
        <img
            src="{{ asset('storage/' . $employee->photo_path) }}"
            alt="{{ $employee->full_name }}"
            class="h-10 w-10 rounded-full object-cover transition-transform duration-300 hover:scale-300">
        @else
        {{-- This creates the dynamic blue circle placeholder --}}
        <div class="h-10 w-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-lg">
            {{-- Gets the first character of the first name --}}
            {{ mb_substr($employee->first_name, 0, 1) }}
        </div>
        @endif
    </td>

    {{-- Full Name Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
        {{ $employee->full_name }}
    </td>

    {{-- Department Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->department->name ?? __('N/A') }}
    </td>

    {{-- Group Column --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->group->name ?? __('N/A') }}
    </td>
</tr>
@empty
<tr>
    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
        {{ __('No employees found.') }}
    </td>
</tr>
@endforelse