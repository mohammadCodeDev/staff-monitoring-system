<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::latest()->get();
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('departments.create');
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
        ]);

        // The spatie package handles the array to JSON conversion automatically
        Department::create(['name' => $validated['name']]);

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
        return view('departments.edit', compact('department'));
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
        ]);

        $department->update(['name' => $validated['name']]);

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
