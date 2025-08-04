<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendance records.
     */
    public function index()
    {
        // Eager load related models to prevent N+1 query issues.
        // We will add role-based filtering here in the future.
        $attendances = Attendance::with(['employee', 'recorder'])->latest()->get();

        return view('attendances.index', compact('attendances'));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create()
    {
        // Fetch only active employees for the selection dropdown
        $employees = Employee::where('is_active', true)->get();
        return view('attendances.create', compact('employees'));
    }

    /**
     * Store a newly created attendance record in storage.
     */
    public function store(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        // Define validation rules
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'event_type' => 'required|in:entry,exit',
            'timestamp' => 'nullable|date', // Timestamp is optional
        ];

        // If the user is a System Admin, the timestamp is required
        if ($user->role->role_name == 'Roles.System Admin') {
            $rules['timestamp'] = 'required|date';
        }

        $validated = $request->validate($rules);

        // Create the attendance record
        Attendance::create([
            'employee_id' => $validated['employee_id'],
            'event_type' => $validated['event_type'],
            // Use provided timestamp, or default to the current time if empty
            'timestamp' => $validated['timestamp'] ?? now(),
            'guard_id' => $user->id, // The logged-in user is the guard
        ]);

        return redirect()->route('attendances.index')
                         ->with('success', __('Attendance recorded successfully.'));
    }
}
