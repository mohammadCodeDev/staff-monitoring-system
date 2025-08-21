{{--
    This partial view renders the rows for the live employee search results.
    It's designed to be fetched via AJAX.
--}}

@forelse ($employees as $employee)
<tr class="hover:bg-gray-100 dark:hover:bg-gray-700">

    {{-- Column 1: Inline Actions (Entry/Exit) --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex items-center space-x-2 rtl:space-x-reverse">
            {{-- Entry Form --}}
            <form action="{{ route('attendances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <input type="hidden" name="event_type" value="entry">
                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none transition">
                    {{ __('Entry') }}
                </button>
            </form>
            {{-- Exit Form --}}
            <form action="{{ route('attendances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                <input type="hidden" name="event_type" value="exit">
                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none transition">
                    {{ __('Exit') }}
                </button>
            </form>
        </div>
    </td>

    {{-- Column 2: Full Name --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
        {{ $employee->fullName }}
    </td>

    {{-- Column 3: Department --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->department?->name }}
    </td>

    {{-- Column 4: Group --}}
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
        {{ $employee->group?->name }}
    </td>

    {{-- Column 5: More Options (Kebab Menu) for Manual Entry --}}
    <td class="px-6 py-4 whitespace-nowrap text-right rtl:text-left text-sm font-medium">
        <div x-data="{ open: false }" class="relative inline-block text-left rtl:text-right">
            <button @click="open = !open" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                </svg>
            </button>
            <div x-show="open" @click.away="open = false"
                x-transition
                class="absolute right-0 rtl:right-auto rtl:left-0 w-48 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none z-10
                       {{-- This Blade directive checks if it's the last item --}}
                       @if($loop->last)
                           origin-bottom-right bottom-full mb-2
                       @else
                           origin-top-right mt-2
                       @endif
                      "
                style="display: none;">
                <div class="py-1">
                    <button @click="$dispatch('open-manual-entry-modal', { employee: {{ json_encode($employee) }} }); open = false;"
                        class="w-full text-left rtl:text-right block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                        {{ __('Manual Entry') }}
                    </button>
                </div>
            </div>
        </div>
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