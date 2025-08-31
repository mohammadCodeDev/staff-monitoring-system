<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AttendanceController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     * This method is updated to group records by employee and day.
     */
    public function index()
    {
        // This query groups attendance records by employee and the calendar date.
        // It finds the earliest 'entry' and the latest 'exit' for each group.
        $attendancePairs = Attendance::query()
            ->select(
                'employee_id',
                DB::raw('DATE(timestamp) as attendance_date'), // Extract the date part for grouping
                DB::raw("MIN(CASE WHEN event_type = 'entry' THEN timestamp END) as entry_time"), // Find the first entry time
                DB::raw("MAX(CASE WHEN event_type = 'exit' THEN timestamp END) as exit_time")   // Find the last exit time
            )
            ->groupBy('employee_id', 'attendance_date')
            ->orderBy('attendance_date', 'desc') // Order the results by date, newest first
            ->orderBy('entry_time', 'desc')      // Also order by entry time
            ->with('employee') // Eager load the employee relationship to prevent N+1 issues
            ->paginate(20); // Paginate the results for better performance

        return view('attendances.index', ['attendances' => $attendancePairs]);
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
        // Authorize the action using the AttendancePolicy.
        $this->authorize('update', $attendance);

        return view('attendances.edit', compact('attendance'));
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        // Authorize the action using the AttendancePolicy.
        $this->authorize('update', $attendance);

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
            return response()->json(['html' => '', 'count' => 0]); // Return JSON
        }

        $employees = Employee::where('is_active', true)
            // This is the new simplified search logic for full name
            ->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%")
            ->with(['department', 'group']) // Eager load relationships for the partial view
            ->latest()
            ->limit(10) // Limit results for better performance
            ->get();

        // Render the partial view to HTML
        $html = view('attendances.partials._search-results-rows', compact('employees'))->render();

        // Return both HTML and the count as a JSON object
        return response()->json([
            'html' => $html,
            'count' => $employees->count()
        ]);
    }

    /**
     * Show the confirmation page for logging attendance for a specific employee.
     */

    /**
     * public function confirm(Employee $employee)
     * {
     *     return view('attendances.confirm', compact('employee'));
     * }
     */

    /**
     * Show the form for manually entering attendance for a specific employee.
     */

    /** 
     * public function manualEntry(Employee $employee)
     * {
     *     return view('attendances.manual-entry', compact('employee'));
     * }
     */

    /**
     * Remove the specified attendance record from storage.
     */
    public function destroy(Attendance $attendance)
    {
        // Authorize the action using the AttendancePolicy.
        $this->authorize('delete', $attendance);

        $attendance->delete();
        return redirect()->route('attendances.index')->with('success', __('Attendance record deleted successfully.'));
    }
}
