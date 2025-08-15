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
        // We no longer need to pass employees here.
        // The view will fetch them via a live search.
        return view('attendances.create');
    }

    /**
     * Store a newly created attendance record in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'event_type' => 'required|in:entry,exit',
            'timestamp' => 'nullable|date',
        ]);

        Attendance::create([
            'employee_id' => $validated['employee_id'],
            'guard_id' => Auth::id(),
            'event_type' => $validated['event_type'],
            'timestamp' => $validated['timestamp'] ?? now(),
        ]);

        // Redirect back with a success message. The view will handle the UI reset.
        return redirect()->route('attendances.create')
            ->with('success', __('Attendance recorded successfully.'));
    }

    public function searchEmployees(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('department', function ($subQ) use ($searchTerm) {
                        $subQ->where('name->en', 'like', "%{$searchTerm}%")
                            ->orWhere('name->fa', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('group', function ($subQ) use ($searchTerm) {
                        $subQ->where('name->en', 'like', "%{$searchTerm}%")
                            ->orWhere('name->fa', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Only get active employees for attendance logging
        $employees = $query->where('is_active', true)->with(['department', 'group'])->latest()->get();

        // Return the correct partial view with the "Select" button
        return view('attendances.partials._search-results-rows', compact('employees'))->render();
    }

    /**
     * Show the confirmation page for logging attendance for a specific employee.
     */
    public function confirm(Employee $employee)
    {
        return view('attendances.confirm', compact('employee'));
    }
}
