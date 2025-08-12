<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $groups = Group::with(['department', 'manager'])->latest()->get();
        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $managers = User::whereHas('role', fn($q) => $q->where('role_name', 'Roles.Group Manager'))->get();
        return view('groups.create', compact('departments', 'managers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.fa' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        Group::create($validated);
        return redirect()->route('groups.index')->with('success', __('Group created successfully.'));
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
     */
    public function edit(Group $group)
    {
        $departments = Department::all();
        $managers = User::whereHas('role', fn($q) => $q->where('role_name', 'Roles.Group Manager'))->get();
        return view('groups.edit', compact('group', 'departments', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $validated = $request->validate([
            'name.en' => 'required|string|max:255',
            'name.fa' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $group->update($validated);
        return redirect()->route('groups.index')->with('success', __('Group updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $group->delete();
        return redirect()->route('groups.index')->with('success', __('Group deleted successfully.'));
    }

    public function getGroupsByDepartment(Department $department)
    {
        return response()->json($department->groups);
    }
}
