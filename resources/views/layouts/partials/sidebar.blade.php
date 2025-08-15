<aside class="w-64 bg-white dark:bg-gray-800 p-4 space-y-6 border-r border-gray-200 dark:border-gray-700 rtl:border-l rtl:border-r-0">

    @if(Auth::user()->role->role_name == 'Roles.System Admin')
        <div>
            <h3 class="font-semibold text-lg mb-4 text-gray-900 dark:text-gray-100 text-left rtl:text-right">{{ __('Admin Actions') }}</h3>
            <div class="flex flex-col space-y-2">
                {{-- Using routeIs() for robust active state checking. '*' is a wildcard. --}}
                <x-sidebar-link :href="route('employees.create')" :active="request()->routeIs('employees.*')">{{ __('Add New Employee') }}</x-sidebar-link>
                <x-sidebar-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">{{ __('View All Employees') }}</x-sidebar-link>
                <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">{{ __('Manage User Roles') }}</x-sidebar-link>
                <x-sidebar-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">{{ __('Manage Departments') }}</x-sidebar-link>
                <x-sidebar-link :href="route('groups.index')" :active="request()->routeIs('groups.*')">{{ __('Manage Groups') }}</x-sidebar-link>
                <x-sidebar-link :href="route('attendances.index')" :active="request()->routeIs('attendances.index')">{{ __('Attendance Monitoring') }}</x-sidebar-link>
                <x-sidebar-link :href="route('attendances.create')" :active="request()->routeIs('attendances.create')">{{ __('Log Attendance') }}</x-sidebar-link>
            </div>
        </div>
    @endif

    @php
        $monitoringRoles = ['Roles.System Observer', 'Roles.University President', 'Roles.Faculty Head', 'Roles.Group Manager'];
    @endphp
    @if(in_array(Auth::user()->role->role_name, $monitoringRoles))
        <div>
            <h3 class="font-semibold text-lg mb-4 text-gray-900 dark:text-gray-100 text-left rtl:text-right">{{ __('Monitoring Actions') }}</h3>
            <div class="flex flex-col space-y-2">
                <x-sidebar-link :href="route('attendances.index')" :active="request()->routeIs('attendances.index')">{{ __('View Attendance Log') }}</x-sidebar-link>
            </div>
        </div>
    @endif

    @if(Auth::user()->role->role_name == 'Roles.Guard')
        <div>
            <h3 class="font-semibold text-lg mb-4 text-gray-900 dark:text-gray-100 text-left rtl:text-right">{{ __('Guard Actions') }}</h3>
            <div class="flex flex-col space-y-2">
                <x-sidebar-link :href="route('attendances.create')" :active="request()->routeIs('attendances.create')">{{ __('Log Attendance') }}</x-sidebar-link>
            </div>
        </div>
    @endif

</aside>
