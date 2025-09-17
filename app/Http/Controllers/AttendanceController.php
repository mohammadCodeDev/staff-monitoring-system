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
use Morilog\Jalali\Jalalian;

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

    public function showChart(Request $request)
    {
        $chartData = $this->getChartDataToday($request);
        return view('attendances.chart', $chartData);
    }

    public function showChartWeek(Request $request)
    {
        $chartData = $this->getChartDataWeek($request);
        return view('attendances.chart', $chartData);
    }

    public function getChartDataToday(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);
        $searchTerm = $request->input('search');

        $query = Attendance::query()
            ->whereDate('timestamp', Carbon::today());

        if ($searchTerm) {
            $query->whereHas('employee', function ($q) use ($searchTerm) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%");
            });
        }

        $todaysEvents = $query->with('employee')->orderBy('employee_id')->orderBy('timestamp')->get();

        // ... (The rest of the pairing and data processing logic is the same as before)
        $groupedByEmployee = $todaysEvents->groupBy('employee_id');
        $attendancePairs = []; // ... (pairing logic)
        foreach ($groupedByEmployee as $events) {
            $entryTime = null;
            $employee = $events->first()->employee;
            if (!$employee) continue;
            foreach ($events as $event) {
                if ($event->event_type === 'entry' && is_null($entryTime)) {
                    $entryTime = $event;
                } elseif ($event->event_type === 'exit' && !is_null($entryTime)) {
                    $attendancePairs[] = (object)['employee' => $employee, 'entry_time' => $entryTime->timestamp, 'exit_time' => $event->timestamp];
                    $entryTime = null;
                }
            }
        }

        $chartData = []; // ... (data processing for chart)
        $employeeNames = [];
        $chartColors = [];
        $employeeColorMap = [];
        $colorPalette = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'];
        $colorIndex = 0;
        foreach ($attendancePairs as $pair) {
            $employeeName = $pair->employee->full_name;
            if (!isset($employeeColorMap[$employeeName])) {
                $employeeNames[] = $employeeName;
                $employeeColorMap[$employeeName] = $colorPalette[$colorIndex % count($colorPalette)];
                $colorIndex++;
            }
            if (is_null($pair->exit_time)) continue;
            $entryCarbon = Carbon::parse($pair->entry_time);
            $exitCarbon = Carbon::parse($pair->exit_time);

            $entryTimestamp = Carbon::create(1970, 1, 1, $entryCarbon->hour, $entryCarbon->minute, $entryCarbon->second, 'UTC')->getTimestamp() * 1000;
            $exitTimestamp = Carbon::create(1970, 1, 1, $exitCarbon->hour, $exitCarbon->minute, $exitCarbon->second, 'UTC')->getTimestamp() * 1000;
            $chartData[] = ['x' => $employeeName, 'y' => [$entryTimestamp, $exitTimestamp]];
            $chartColors[] = $employeeColorMap[$employeeName];
        }

        $series = [['name' => __('Working Hours'), 'data' => $chartData]];

        $data = [
            'series' => $series,
            'categories' => $employeeNames,
            'chartColors' => $chartColors,
            'viewType' => 'today',
            'startDateFormatted' => null,
            'endDateFormatted' => null,
            'searchTerm' => $searchTerm,
        ];

        // If the request is AJAX, return JSON, otherwise return the array for the view.
        if ($request->ajax()) {
            return response()->json($data);
        }
        return $data;
    }

    public function getChartDataWeek(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);
        $searchTerm = $request->input('search');

        $query = Attendance::query()
            ->where('timestamp', '>=', Carbon::now()->subDays(6)->startOfDay());

        if ($searchTerm) {
            $query->whereHas('employee', function ($q) use ($searchTerm) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%");
            });
        }

        $weeklyEvents = $query->with('employee')->orderBy('timestamp')->get();

        // ... (The rest of the weekly data processing logic is the same as before)
        $groupedByEmployee = $weeklyEvents->groupBy('employee_id');
        $attendancePairs = []; // ... (pairing logic)
        foreach ($groupedByEmployee as $events) {
            $entryTime = null;
            $employee = $events->first()->employee;
            if (!$employee) continue;
            foreach ($events as $event) {
                if ($event->event_type === 'entry' && is_null($entryTime)) {
                    $entryTime = $event;
                } elseif ($event->event_type === 'exit' && !is_null($entryTime)) {
                    $attendancePairs[] = (object)['employee' => $employee, 'entry_time' => $entryTime->timestamp, 'exit_time' => $event->timestamp];
                    $entryTime = null;
                }
            }
        }

        $dayCategories = []; // ... (day category generation)
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayCategories[$date->toDateString()] = __($date->format('l')) . ' (' . $date->format('Y-m-d') . ')';
        }

        $pairsByEmployee = collect($attendancePairs)->groupBy('employee.id');
        $series = []; // ... (series generation logic)
        $employeeColorMap = [];
        $colorPalette = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'];
        $colorIndex = 0;
        foreach ($pairsByEmployee as $employeeId => $employeePairs) {
            $employee = $employeePairs->first()->employee;
            $employeeName = $employee->full_name;
            if (!isset($employeeColorMap[$employeeName])) {
                $employeeColorMap[$employeeName] = $colorPalette[$colorIndex % count($colorPalette)];
                $colorIndex++;
            }
            $employeeDataPoints = [];
            foreach ($employeePairs as $pair) {
                $dateString = Carbon::parse($pair->entry_time)->toDateString();
                if (isset($dayCategories[$dateString])) {
                    $entryTime = Carbon::parse($pair->entry_time);
                    $exitTime = Carbon::parse($pair->exit_time);
                    $entryTimestamp = Carbon::create(1970, 1, 1, $entryTime->hour, $entryTime->minute, $entryTime->second, 'UTC')->getTimestamp() * 1000;
                    $exitTimestamp = Carbon::create(1970, 1, 1, $exitTime->hour, $exitTime->minute, $exitTime->second, 'UTC')->getTimestamp() * 1000;
                    $employeeDataPoints[] = ['x' => $dayCategories[$dateString], 'y' => [$entryTimestamp, $exitTimestamp]];
                }
            }
            $series[] = ['name' => $employeeName, 'data' => $employeeDataPoints];
        }

        $startDateFormatted = Carbon::now()->subDays(6)->translatedFormat('l (Y-m-d)');
        $endDateFormatted = Carbon::now()->translatedFormat('l (Y-m-d)');

        $data = [
            'series' => $series,
            'categories' => array_values($dayCategories),
            'chartColors' => array_values($employeeColorMap),
            'viewType' => 'week',
            'startDateFormatted' => $startDateFormatted,
            'endDateFormatted' => $endDateFormatted,
            'searchTerm' => $searchTerm,
        ];

        if ($request->ajax()) {
            return response()->json($data);
        }
        return $data;
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




    public function showChartMonth(Request $request)
    {
        $chartData = $this->getChartDataMonth($request);
        return view('attendances.chart', $chartData);
    }

    public function getChartDataMonth(Request $request)
    {
        $this->authorize('viewAny', Attendance::class);
        $searchTerm = $request->input('search');

        // Query for all events from the beginning of the current month until now
        $query = Attendance::query()
            ->where('timestamp', '>=', Carbon::now()->startOfMonth());

        if ($searchTerm) {
            $query->whereHas('employee', function ($q) use ($searchTerm) {
                $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%");
            });
        }
        $monthlyEvents = $query->with('employee')->orderBy('timestamp')->get();

        // The rest of the logic is very similar to the weekly view
        $groupedByEmployee = $monthlyEvents->groupBy('employee_id');
        $attendancePairs = [];
        foreach ($groupedByEmployee as $events) {
            $entryTime = null;
            $employee = $events->first()->employee;
            if (!$employee) continue;
            foreach ($events as $event) {
                if ($event->event_type === 'entry' && is_null($entryTime)) {
                    $entryTime = $event;
                } elseif ($event->event_type === 'exit' && !is_null($entryTime)) {
                    $attendancePairs[] = (object)['employee' => $employee, 'entry_time' => $entryTime->timestamp, 'exit_time' => $event->timestamp];
                    $entryTime = null;
                }
            }
        }

        // Y-axis categories will be the days of the month so far
        $dayCategories = [];
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now();
        for ($date = $startOfMonth; $date->lte($today); $date->addDay()) {
            $dayCategories[$date->toDateString()] = $date->format('Y-m-d');
        }

        $pairsByEmployee = collect($attendancePairs)->groupBy('employee.id');
        $series = [];
        $employeeColorMap = [];
        $colorPalette = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'];
        $colorIndex = 0;

        foreach ($pairsByEmployee as $employeeId => $employeePairs) {
            $employee = $employeePairs->first()->employee;
            $employeeName = $employee->full_name;
            if (!isset($employeeColorMap[$employeeName])) {
                $employeeColorMap[$employeeName] = $colorPalette[$colorIndex % count($colorPalette)];
                $colorIndex++;
            }
            $employeeDataPoints = [];
            foreach ($employeePairs as $pair) {
                $dateString = Carbon::parse($pair->entry_time)->toDateString();
                if (isset($dayCategories[$dateString])) {
                    $entryCarbon = Carbon::parse($pair->entry_time);
                    $exitCarbon = Carbon::parse($pair->exit_time);
                    $entryTimestamp = Carbon::create(1970, 1, 1, $entryCarbon->hour, $entryCarbon->minute, $entryCarbon->second, 'UTC')->getTimestamp() * 1000;
                    $exitTimestamp = Carbon::create(1970, 1, 1, $exitCarbon->hour, $exitCarbon->minute, $exitCarbon->second, 'UTC')->getTimestamp() * 1000;
                    $employeeDataPoints[] = ['x' => $dayCategories[$dateString], 'y' => [$entryTimestamp, $exitTimestamp]];
                }
            }
            $series[] = ['name' => $employeeName, 'data' => $employeeDataPoints];
        }

        // Calculate start and end dates for the title
        $startDateFormatted = Carbon::now()->startOfMonth()->translatedFormat('l (Y-m-d)');
        $endDateFormatted = Carbon::now()->translatedFormat('l (Y-m-d)');

        $data = [
            'series' => $series,
            'categories' => array_values($dayCategories),
            'chartColors' => array_values($employeeColorMap),
            'viewType' => 'month',
            'startDateFormatted' => $startDateFormatted, // Use the calculated start date
            'endDateFormatted' => $endDateFormatted,   // Use the calculated end date
            'searchTerm' => $searchTerm
        ];

        if ($request->ajax()) {
            return response()->json($data);
        }
        return $data;
    }

    /**
     * Display the second attendance chart view.
     *
     * @return \Illuminate\View\View
     */
    public function chart2()
    {
        return view('attendances.chart2');
    }

    public function searchEmployees2(Request $request)
    {
        $searchTerm = $request->input('search', '');
        // ADD THIS LINE: Get the context/view type from the request
        $viewType = $request->input('view', 'log'); // Default to 'log' for the original page

        if (empty($searchTerm)) {
            return response()->json(['html' => '', 'count' => 0]);
        }

        $employees = Employee::where('is_active', true)
            ->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$searchTerm}%")
            ->with(['department', 'group'])
            ->latest()
            ->limit(10)
            ->get();

        // THIS IS THE NEW LOGIC: Choose the partial view based on the viewType
        $partialView = $viewType === 'chart'
            ? 'attendances.partials._chart-search-results-rows'  // The new partial for our chart page
            : 'attendances.partials._search-results-rows';       // The original partial for the log page

        // Render the chosen partial view to HTML
        $html = view($partialView, compact('employees'))->render();

        return response()->json([
            'html' => $html,
            'count' => $employees->count()
        ]);
    }
}
