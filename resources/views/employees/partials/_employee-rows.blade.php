@forelse ($employees as $employee)
    <tr>
        <td class="px-6 py-4 whitespace-nowrap">{{ $employee->fullName }}</td>
        <td class="px-6 py-4 whitespace-nowrap">{{ __($employee->department->name) }}</td>
        <td class="px-6 py-4 whitespace-nowrap">
            @if($employee->is_active)
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-200 text-green-800">{{ __('Active') }}</span>
            @else
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-200 text-red-800">{{ __('Inactive') }}</span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="flex justify-start space-x-4 rtl:space-x-reverse">
                {{-- Action Buttons --}}
                <a href="{{ route('employees.edit', $employee->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200">{{ __('Edit') }}</a>
                @if($employee->is_active)
                    <form action="{{ route('employees.deactivate', $employee->id) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to deactivate this employee?') }}')) $el.submit()">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-200">{{ __('Deactivate') }}</button>
                    </form>
                @else
                    <form action="{{ route('employees.reactivate', $employee->id) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to reactivate this employee?') }}')) $el.submit()">
                        @csrf @method('PATCH')
                        <button type="submit" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200">{{ __('Reactivate') }}</button>
                    </form>
                @endif
                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" x-data @submit.prevent="if (confirm('{{ __('Are you sure you want to permanently delete this employee? This action cannot be undone.') }}')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">{{ __('Delete') }}</button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">{{ __('No employees found.') }}</td>
    </tr>
@endforelse