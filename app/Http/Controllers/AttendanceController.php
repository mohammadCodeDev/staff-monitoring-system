<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        // *** THE FIX IS HERE: More specific validation rule ***
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'event_type' => 'required|in:entry,exit',
            // This rule specifically matches the format from datetime-local input
            'timestamp' => 'nullable|date_format:Y-m-d\TH:i',
        ]);

        $timestampToStore = null;

        // The key 'timestamp' will only exist in $validated if it's not empty and passes validation.
        if (isset($validated['timestamp'])) {
            // Use createFromFormat for stricter, more reliable parsing.
            $timestampToStore = Carbon::createFromFormat('Y-m-d\TH:i', $validated['timestamp']);
        } else {
            // This branch is for the 'confirm' page which doesn't send a timestamp.
            $timestampToStore = now();
        }

        Attendance::create([
            'employee_id' => $validated['employee_id'],
            'guard_id' => Auth::id(),
            'event_type' => $validated['event_type'], // Using validated data is safer
            'timestamp' => $timestampToStore,
        ]);

        // Redirect back to the search page with a success message
        return redirect()->route('attendances.create')
            ->with('success', __('Attendance recorded successfully.'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        return view('attendances.edit', compact('attendance'));
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'timestamp' => 'required|date_format:Y-m-d\TH:i',
        ]);

        $attendance->update([
            'timestamp' => Carbon::createFromFormat('Y-m-d\TH:i', $validated['timestamp']),
        ]);

        return redirect()->route('attendances.index')->with('success', __('Attendance record updated successfully.'));
    }

    public function searchEmployees(Request $request)
    {
        $searchTerm = $request->input('search', '');

        // If the search term is empty, return an empty response to clear the results.
        if (empty($searchTerm)) {
            return response('');
        }

        $employees = Employee::where('is_active', true)
            // This is the new simplified search logic for full name
            ->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%")
            ->with(['department', 'group']) // Eager load relationships for the partial view
            ->latest()
            ->limit(10) // Limit results for better performance
            ->get();

        // Return the partial view with the found employees
        // No changes are needed in the partial view itself.
        return view('attendances.partials._search-results-rows', compact('employees'))->render();
    }

    /**
     * Show the confirmation page for logging attendance for a specific employee.
     */
    public function confirm(Employee $employee)
    {
        return view('attendances.confirm', compact('employee'));
    }

    /**
     * Show the form for manually entering attendance for a specific employee.
     */
    public function manualEntry(Employee $employee)
    {
        return view('attendances.manual-entry', compact('employee'));
    }

    /**
     * Remove the specified attendance record from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendances.index')->with('success', __('Attendance record deleted successfully.'));
    }
}
