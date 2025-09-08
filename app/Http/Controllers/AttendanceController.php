<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

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
     * This method now includes advanced validation logic.
     */
    public function store(Request $request)
    {
        $input = $request->all();

        // Step 1: Basic validation using the Validator facade
        $validator = Validator::make($input, [
            'employee_id' => 'required|exists:employees,id',
            'event_type' => 'required|in:entry,exit',
            'timestamp' => 'nullable|date_format:Y-m-d\TH:i',
        ]);

        // Step 2: Add complex conditional validation using the 'after' hook
        $validator->after(function ($validator) use ($input) {
            // Get the employee ID and event type from the input
            $employeeId = $input['employee_id'] ?? null;
            $eventType = $input['event_type'] ?? null;

            if (!$employeeId || !$eventType) {
                return; // Stop if basic data is missing
            }

            // Find the last recorded event for this employee
            $lastEvent = Attendance::where('employee_id', $employeeId)->latest('timestamp')->first();

            // Rule: Cannot log an 'exit' if there is no previous record OR if the last event was already an 'exit'.
            if ($eventType === 'exit') {
                if (!$lastEvent || $lastEvent->event_type === 'exit') {
                    // Add a custom error message for this specific case
                    $validator->errors()->add(
                        'event_type',
                        __('validation.attendance.exit_before_entry')
                    );
                }
            }

            // Rule: Cannot log an 'entry' if the last event was already an 'entry'
            if ($eventType === 'entry') {
                if ($lastEvent && $lastEvent->event_type === 'entry') {
                    // Add a custom error message for this case
                    $validator->errors()->add(
                        'event_type',
                        __('validation.attendance.duplicate_entry')
                    );
                }
            }
        });

        // Step 3: Check if validation fails
        if ($validator->fails()) {
            // Redirect back with the validation errors and the original input
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Step 4: If validation passes, proceed to create the record
        $validated = $validator->validated(); // Get the validated data

        $timestampToStore = isset($validated['timestamp'])
            ? Carbon::createFromFormat('Y-m-d\TH:i', $validated['timestamp'])
            : now();

        Attendance::create([
            'employee_id' => $validated['employee_id'],
            'guard_id' => Auth::id(),
            'event_type' => $validated['event_type'],
            'timestamp' => $timestampToStore,
        ]);

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

        return redirect()->route('attendances.raw-log')->with('success', __('Attendance record updated successfully.'));
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
        return redirect()->route('attendances.raw-log')->with('success', __('Attendance record deleted successfully.'));
    }

    /**
     * Display a raw, detailed listing of all attendance records.
     * This method is for the detailed log view.
     */
    public function rawLog()
    {
        // Authorize if the user can view any attendance records at all.
        // This uses the 'viewAny' method in your AttendancePolicy (if it exists).
        $this->authorize('viewAny', Attendance::class);

        // Fetch all attendance records, ordered by the latest timestamp.
        // Eager load related models to prevent N+1 query issues.
        $attendances = Attendance::with(['employee', 'recorder'])
            ->latest() // This is equivalent to orderBy('created_at', 'desc')
            ->paginate(25); // Paginate the raw log as well

        // The view name matches our new file name.
        return view('attendances.raw-log', compact('attendances'));
    }

   /**
     * Display attendance data as a chart for the current day.
     * The Y-axis will show employees and the X-axis will show a 24-hour timeline.
     * This version assigns a unique color to each employee.
     */
    public function showChart()
    {
        // Authorize if the user can view any attendance records.
        $this->authorize('viewAny', Attendance::class);

        // Fetch today's attendance records.
        $todaysAttendance = Attendance::query()
            ->whereDate('timestamp', Carbon::today())
            ->select(
                'employee_id',
                DB::raw("MIN(CASE WHEN event_type = 'entry' THEN timestamp END) as entry_time"),
                DB::raw("MAX(CASE WHEN event_type = 'exit' THEN timestamp END) as exit_time")
            )
            ->groupBy('employee_id')
            ->with('employee')
            ->get();

        // Process data into pairs.
        $chartData = [];
        $employeeNames = [];

        foreach ($todaysAttendance as $record) {
            if (!$record->entry_time || !$record->exit_time || !$record->employee) {
                continue;
            }

            $employeeName = $record->employee->full_name;
            $employeeNames[] = $employeeName;

            $baseDate = '1970-01-01';
            $entryTime = Carbon::parse($record->entry_time)->format('H:i:s');
            $exitTime = Carbon::parse($record->exit_time)->format('H:i:s');
            $entryTimestamp = Carbon::parse("$baseDate $entryTime")->getTimestamp() * 1000;
            $exitTimestamp = Carbon::parse("$baseDate $exitTime")->getTimestamp() * 1000;

            $chartData[] = [
                'x' => $employeeName,
                'y' => [$entryTimestamp, $exitTimestamp],
            ];
        }
        
        // --- START: NEW CODE FOR COLORS ---
        // Step A: Define a palette of colors.
        $colorPalette = [
            '#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0',
            '#546E7A', '#26a69a', '#D10CE8', '#FF66C3', '#00B0F0'
        ];
        
        // Step B: Generate a color for each employee, looping through the palette if needed.
        $chartColors = [];
        for ($i = 0; $i < count($employeeNames); $i++) {
            $chartColors[] = $colorPalette[$i % count($colorPalette)];
        }
        // --- END: NEW CODE FOR COLORS ---

        // Create the final series object.
        $series = [
            [
                'name' => __('Working Hours'),
                'data' => $chartData,
            ]
        ];

        // Step C: Pass the new colors array to the view.
        return view('attendances.chart', [
            'series' => $series,
            'categories' => $employeeNames,
            'chartColors' => $chartColors, // New variable passed to the view
        ]);
    }

    /**
     * Display a listing of the attendance resource for the current day on a dedicated page.
     * This version correctly handles multiple entry/exit pairs for a single employee.
     */
    public function showToday()
    {
        // Authorize if the user can view any attendance records.
        $this->authorize('viewAny', Attendance::class);

        // Step 1: Fetch all of today's raw events, ordered correctly by employee and time.
        $todaysEvents = Attendance::query()
            ->whereDate('timestamp', Carbon::today())
            ->with('employee')
            ->orderBy('employee_id')
            ->orderBy('timestamp')
            ->get();

        // Step 2: Group events by employee and process them to create entry/exit pairs.
        $groupedByEmployee = $todaysEvents->groupBy('employee_id');
        $attendancePairs = [];

        foreach ($groupedByEmployee as $events) {
            $entryTime = null;
            $employee = $events->first()->employee;

            if (!$employee) continue; // Skip if for some reason employee is not found

            foreach ($events as $event) {
                if ($event->event_type === 'entry' && is_null($entryTime)) {
                    // Found the start of a new session
                    $entryTime = $event;
                } elseif ($event->event_type === 'exit' && !is_null($entryTime)) {
                    // Found the end of the session, create a pair object
                    $attendancePairs[] = (object)[
                        'employee' => $employee,
                        'entry_time' => $entryTime->timestamp,
                        'exit_time' => $event->timestamp,
                        'attendance_date' => Carbon::parse($event->timestamp)->toDateString()
                    ];
                    $entryTime = null; // Reset for the next potential pair
                }
            }

            // This handles the case where an employee has an 'entry' but no 'exit' yet for today.
            if ($entryTime) {
                $attendancePairs[] = (object)[
                    'employee' => $employee,
                    'entry_time' => $entryTime->timestamp,
                    'exit_time' => null, // No exit time yet
                    'attendance_date' => Carbon::parse($entryTime->timestamp)->toDateString()
                ];
            }
        }

        // Pass the processed pairs to the view. Note: Pagination is removed as it's a single day view.
        return view('attendances.today', ['attendances' => collect($attendancePairs)]);
    }
}
