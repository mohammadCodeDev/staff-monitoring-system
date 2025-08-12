<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{-- Using the employee's name in the title --}}
                {{ __('Edit Employee') . ': ' . $employee->fullName }}
            </h2>

            {{-- Link to go back to the employee list --}}
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                {{ __('View All Employees') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{
        selectedDepartment: '{{ old('department_id', $employee->department_id ?? '') }}',
        selectedGroup: '{{ old('group_id', $employee->group_id ?? '') }}',
        groups: [],
        isLoading: false,
        fetchGroups() {
            // If no department is selected, clear both the group options and the selected group.
            if (!this.selectedDepartment) {
                this.groups = [];
                this.selectedGroup = ''; // <-- This is the crucial line you need to add/ensure is present.
                return;
            }

            this.isLoading = true;
            fetch(`/api/departments/${this.selectedDepartment}/groups`)
                .then(res => res.json())
                .then(data => {
                    this.groups = data;
                    this.isLoading = false;
                    // Important: Check if the current selected group is valid for the new department
                    const groupIds = this.groups.map(g => g.id);
                    if (!groupIds.includes(parseInt(this.selectedGroup))) {
                        this.selectedGroup = '';
                    }
            });
        },
        init() {
            if(this.selectedDepartment) this.fetchGroups();
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- The form points to the update route and uses the PUT method --}}
                    <form action="{{ route('employees.update', $employee->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT') {{-- Important for telling Laravel this is an update operation --}}

                        {{-- First Name --}}
                        <div>
                            <x-input-label for="first_name" :value="__('First Name')" />
                            {{-- The value is pre-filled with existing data --}}
                            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $employee->first_name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                        </div>

                        {{-- Last Name --}}
                        <div>
                            <x-input-label for="last_name" :value="__('Last Name')" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $employee->last_name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                        </div>

                        {{-- Department --}}
                        <div>
                            <x-input-label for="department_id" :value="__('Department')" />
                            <select id="department_id" name="department_id" x-model="selectedDepartment" @change="fetchGroups()" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                {{-- placeholder option --}}
                                <option value="">{{ __('Select a Department') }}</option>
                                @foreach ($departments as $department)
                                {{-- The employee's current department is pre-selected --}}
                                <option value="{{ $department->id }}" @selected(old('department_id', $employee->department_id) == $department->id)>
                                    {{ __($department->name) }}
                                </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
                        </div>

                        {{-- Group Selection (Dynamic) --}}
                        <div>
                            <x-input-label for="group_id" :value="__('Group Name')" />
                            <select id="group_id" name="group_id" x-model="selectedGroup" :disabled="isLoading || !selectedDepartment" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">{{ __('Select a Group') }}</option>
                                <template x-if="isLoading">
                                    <option disabled>{{ __('Loading...') }}</option>
                                </template>
                                <template x-for="group in groups" :key="group.id">
                                    <option :value="group.id" x-text="group.name.{{ app()->getLocale() }}"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Status (Active/Inactive) --}}
                        <div>
                            <x-input-label for="is_active" :value="__('Status')" />
                            <select id="is_active" name="is_active" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="1" @selected(old('is_active', $employee->is_active) == 1)>{{ __('Active') }}</option>
                                <option value="0" @selected(old('is_active', $employee->is_active) == 0)>{{ __('Inactive') }}</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Update Employee') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>