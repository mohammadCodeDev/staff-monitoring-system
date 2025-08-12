<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load the manager to prevent N+1 query problem
        $departments = Department::with('manager')->latest()->get();
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all users who have the 'Faculty Head' role to be potential managers
        $managers = User::whereHas('role', function ($query) {
            $query->where('role_name', 'Roles.Faculty Head');
        })->get();

        return view('departments.create', compact('managers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Updated validation for translatable fields
        $validated = $request->validate([
            'name.en' => 'required|string|max:255|unique:departments,name->en',
            'name.fa' => 'required|string|max:255|unique:departments,name->fa',
            'manager_id' => 'nullable|exists:users,id' // Validate the manager
        ]);

        // The spatie package handles the array to JSON conversion automatically
        Department::create([
            'name' => $validated['name'],
            'manager_id' => $request->manager_id,
        ]);

        return redirect()->route('departments.index')->with('success', __('Department created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        // Also fetch potential managers for the edit form
        $managers = User::whereHas('role', function ($query) {
            $query->where('role_name', 'Roles.Faculty Head');
        })->get();

        return view('departments.edit', compact('department', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        // Updated validation for translatable fields, ignoring the current department
        $validated = $request->validate([
            'name.en' => 'required|string|max:255|unique:departments,name->en,' . $department->id,
            'name.fa' => 'required|string|max:255|unique:departments,name->fa,' . $department->id,
            'manager_id' => 'nullable|exists:users,id' // Validate the manager
        ]);

        $department->update([
            'name' => $validated['name'],
            'manager_id' => $request->manager_id,
        ]);

        return redirect()->route('departments.index')->with('success', __('Department updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Add a check to prevent deletion if employees are associated with it (optional but recommended)
        if ($department->employees()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete department with associated employees.']);
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', __('Department deleted successfully.'));
    }
}
