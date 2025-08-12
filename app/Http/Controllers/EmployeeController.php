<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start with a query builder instance
        $query = Employee::query();

        // Check if a search term is provided in the request
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    // Search in both English and Persian translations of the department name
                    ->orWhereHas('department', function ($subQ) use ($searchTerm) {
                        $subQ->where('name->en', 'like', "%{$searchTerm}%")
                            ->orWhere('name->fa', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('group', function ($subQ) use ($searchTerm) { // <-- Search by group
                        $subQ->where('name->en', 'like', "%{$searchTerm}%")
                            ->orWhere('name->fa', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Eager load the department relationship and get the results
        $employees = $query->with('department')->latest()->get();

        // If the request is an AJAX request, return only the table rows partial.
        if ($request->ajax()) {
            return view('employees.partials._employee-rows', compact('employees'))->render();
        }

        // Otherwise, return the full page view.
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all departments to display in the form's dropdown menu
        $departments = Department::all();

        // We don't pass groups here, they will be fetched via API
        return view('employees.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate the data coming from the form
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id', // Ensures the selected department is valid
            'department_id' => 'nullable|exists:departments,id', // <-- Make nullable
            'group_id' => 'nullable|exists:groups,id',           // <-- Add group_id
        ]);

        // 2. Create a new employee record using the validated data
        Employee::create($validatedData);

        // 3. Redirect the user to the employee list page with a success message
        // We will create the index (list) page in the next step.
        return redirect()->route('employees.index')
            ->with('success', __('Employee/Professor created successfully!'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * Laravel's Route-Model Binding automatically finds the employee.
     */
    public function edit(Employee $employee)
    {
        // We need the list of all departments for the dropdown menu
        $departments = Department::all();

        // Return the edit view, passing the specific employee and all departments
        return view('employees.edit', compact('employee', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        // 1. Validate the incoming data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'department_id' => 'nullable|exists:departments,id', // <-- Make nullable
            'group_id' => 'nullable|exists:groups,id',           // <-- Add group_id
            'is_active' => 'required|boolean', // Validate the status
        ]);

        // 2. Update the employee's record with the validated data
        $employee->update($validatedData);

        // 3. Redirect back to the employee list with a success message
        return redirect()->route('employees.index')
            ->with('success', __('Employee updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', __('Employee deleted successfully.'));
    }
    /**
     * Deactivate the specified employee.
     */
    public function deactivate(Employee $employee)
    {
        // Set the is_active flag to false
        $employee->update(['is_active' => false]);

        // Redirect back to the list with a success message
        return redirect()->route('employees.index')
            ->with('success', __('Employee deactivated successfully.'));
    }

    /**
     * Reactivate the specified employee.
     */
    public function reactivate(Employee $employee)
    {
        $employee->update(['is_active' => true]);
        return redirect()->route('employees.index')
            ->with('success', __('Employee reactivated successfully.'));
    }
}
