<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all employees from the database.
        // 'with('department')' prevents the N+1 query problem by loading the department relationship eagerly.
        // 'latest()' orders the results to show the most recently created employees first.
        $employees = Employee::with('department')->latest()->get();

        // Return the view and pass the employees data to it.
        return view('employees.index', ['employees' => $employees]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all departments to display in the form's dropdown menu
        $departments = Department::all();

        // Return the view and pass the departments data to it
        return view('employees.create', ['departments' => $departments]);
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
        ]);

        // 2. Create a new employee record using the validated data
        Employee::create($validatedData);

        // 3. Redirect the user to the employee list page with a success message
        // We will create the index (list) page in the next step.
        return redirect()->route('employees.index')
            ->with('success', 'Employee/Professor created successfully!');
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
    public function destroy(string $id)
    {
        //
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
}
