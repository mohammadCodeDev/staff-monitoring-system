<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start with a query builder instance
        $query = Employee::query();

        // Check if a search term is provided in the request
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name', 'like', "%{$searchTerm}%")
                    // Search in both English and Persian translations of the department name
                    ->orWhereHas('department', function ($subQ) use ($searchTerm) {
                        $subQ->where('name->en', 'like', "%{$searchTerm}%")
                            ->orWhere('name->fa', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('group', function ($subQ) use ($searchTerm) { // <-- Search by group
                        $subQ->where('name->en', 'like', "%{$searchTerm}%")
                            ->orWhere('name->fa', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Eager load the department relationship and get the results
        $employees = $query->with('department')->latest()->get();

        // If the request is an AJAX request, return only the table rows partial.
        if ($request->ajax()) {
            return view('employees.partials._employee-rows', compact('employees'))->render();
        }

        // Otherwise, return the full page view.
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all departments to display in the form's dropdown menu
        $departments = Department::all();

        // We don't pass groups here, they will be fetched via API
        return view('employees.create', compact('departments'));
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
            'department_id' => 'nullable|exists:departments,id', // <-- Make nullable
            'group_id' => 'nullable|exists:groups,id',           // <-- Add group_id
        ]);

        // If no department is selected, ensure the group is also set to null.
        if (empty($validatedData['department_id'])) {
            $validatedData['group_id'] = null;
        }

        // 2. Create a new employee record using the validated data
        Employee::create($validatedData);

        // 3. Redirect the user to the employee list page with a success message
        // We will create the index (list) page in the next step.
        return redirect()->route('employees.index')
            ->with('success', __('Employee/Professor created successfully!'));
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
            'department_id' => 'nullable|exists:departments,id', // <-- Make nullable
            'group_id' => 'nullable|exists:groups,id',           // <-- Add group_id
            'is_active' => 'required|boolean', // Validate the status
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB Max
        ]);


        // --- Handle File Update ---
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($employee->profile_photo_path) {
                Storage::disk('public')->delete($employee->profile_photo_path);
            }
            // Store the new photo
            $path = $request->file('profile_photo')->store('employee_photos', 'public');
            $validatedData['profile_photo_path'] = $path;
        }

        if (empty($validatedData['department_id'])) {
            $validatedData['group_id'] = null;
        }

        // 2. Update the employee's record with the validated data
        $employee->update($validatedData);

        // 3. Redirect back to the employee list with a success message
        return redirect()->route('employees.index')
            ->with('success', __('Employee updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        // --- Delete Photo from Storage ---
        if ($employee->profile_photo_path) {
            Storage::disk('public')->delete($employee->profile_photo_path);
        }

        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', __('Employee deleted successfully.'));
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

    /**
     * Reactivate the specified employee.
     */
    public function reactivate(Employee $employee)
    {
        $employee->update(['is_active' => true]);
        return redirect()->route('employees.index')
            ->with('success', __('Employee reactivated successfully.'));
    }

    public function showMonthlyReport(Request $request, Employee $employee, $year = null, $month = null)
    {
        if ($year && $month) {
            $targetDate = Carbon::createFromDate($year, $month, 1);
        } else {
            $targetDate = Carbon::now();
        }

        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('timestamp', [$startOfMonth, $endOfMonth])
            ->orderBy('timestamp', 'asc')
            ->get();

        // --- NEW LOGIC: Generate a complete list of all days in the month ---
        $allDaysOfMonth = [];
        $currentDay = $startOfMonth->copy();
        while ($currentDay->lte($endOfMonth)) {
            $allDaysOfMonth[] = $currentDay->format('Y-m-d');
            $currentDay->addDay();
        }
        // --- END OF NEW LOGIC ---

        $chartData = $this->processAttendanceForChart($attendances);

        return view('employees.reports.monthly', [
            'employee' => $employee,
            'chartSeries' => $chartData['series'],
            'chartCategories' => $allDaysOfMonth, // Pass the complete list of days to the view
            'targetDate' => $targetDate,
        ]);
    }

    /**
     * Processes raw attendance records into a format suitable for the ApexCharts timeline.
     * THIS IS THE FINAL, CORRECTED VERSION.
     */
    private function processAttendanceForChart($attendances)
    {
        if ($attendances->isEmpty()) {
            // Return an empty structure for the series.
            return ['series' => [
                ['name' => 'Working Hours', 'data' => []],
                ['name' => 'Entry', 'data' => []],
                ['name' => 'Exit', 'data' => []],
            ]];
        }

        $seriesData = [
            'bars' => [],
            'entries' => [],
            'exits' => [],
        ];
        $lastEntry = null;

        // This simplified version no longer creates categories. It only processes events.
        foreach ($attendances as $event) {
            $eventTime = Carbon::parse($event->timestamp);
            $dateCategory = $eventTime->format('Y-m-d');
            $timeValue = Carbon::create(1970, 1, 1, $eventTime->hour, $eventTime->minute, $eventTime->second, 'UTC');

            if ($event->event_type === 'entry') {
                if ($lastEntry) {
                    $lastEntryTime = Carbon::parse($lastEntry->timestamp);
                    $entryTimeValue = Carbon::create(1970, 1, 1, $lastEntryTime->hour, $lastEntryTime->minute, $lastEntryTime->second, 'UTC');
                    $seriesData['entries'][] = ['x' => $lastEntryTime->format('Y-m-d'), 'y' => [$entryTimeValue->timestamp * 1000, $entryTimeValue->clone()->addMinutes(5)->timestamp * 1000]];
                }
                $lastEntry = $event;
            }

            if ($event->event_type === 'exit') {
                if ($lastEntry) {
                    $entryTime = Carbon::parse($lastEntry->timestamp);
                    $entryTimeValue = Carbon::create(1970, 1, 1, $entryTime->hour, $entryTime->minute, $entryTime->second, 'UTC');
                    $seriesData['bars'][] = ['x' => $dateCategory, 'y' => [$entryTimeValue->timestamp * 1000, $timeValue->timestamp * 1000]];
                    $lastEntry = null;
                } else {
                    $seriesData['exits'][] = ['x' => $dateCategory, 'y' => [$timeValue->timestamp * 1000, $timeValue->clone()->addMinutes(5)->timestamp * 1000]];
                }
            }
        }

        if ($lastEntry) {
            $lastEntryTime = Carbon::parse($lastEntry->timestamp);
            $entryTimeValue = Carbon::create(1970, 1, 1, $lastEntryTime->hour, $lastEntryTime->minute, $lastEntryTime->second, 'UTC');
            $seriesData['entries'][] = ['x' => $lastEntryTime->format('Y-m-d'), 'y' => [$entryTimeValue->timestamp * 1000, $entryTimeValue->clone()->addMinutes(5)->timestamp * 1000]];
        }

        $finalSeries = [
            ['name' => 'Working Hours', 'data' => $seriesData['bars']],
            ['name' => 'Entry', 'data' => $seriesData['entries']],
            ['name' => 'Exit', 'data' => $seriesData['exits']],
        ];

        // This method now only returns the series data.
        return ['series' => $finalSeries];
    }

    public function showYearlyReport(Request $request, Employee $employee, $year = null)
    {
        $targetYear = $year ?? Carbon::now()->year;
        $targetDate = Carbon::createFromDate($targetYear, 1, 1);

        $startOfYear = $targetDate->copy()->startOfYear();
        $endOfYear = $targetDate->copy()->endOfYear();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('timestamp', [$startOfYear, $endOfYear])
            ->orderBy('timestamp', 'asc')
            ->get();

        $monthlyTotals = array_fill(1, 12, 0);
        $lastEntry = null;

        foreach ($attendances as $event) {
            if ($event->event_type === 'entry') {
                $lastEntry = $event;
            }

            if ($event->event_type === 'exit' && $lastEntry) {
                $entryTime = Carbon::parse($lastEntry->timestamp);
                $exitTime = Carbon::parse($event->timestamp);

                if ($entryTime->isSameDay($exitTime)) {
                    $durationInSeconds = $entryTime->diffInSeconds($exitTime);
                    $month = $entryTime->month;
                    $monthlyTotals[$month] += $durationInSeconds;
                }
                $lastEntry = null;
            }
        }

        $monthlyHours = [];
        foreach ($monthlyTotals as $seconds) {
            $monthlyHours[] = round($seconds / 3600, 2);
        }

        // --- REPLACED JALALI LOGIC WITH GREGORIAN ---
        // Create standard English month names for the chart categories.
        $gregorianMonths = [];
        for ($m = 1; $m <= 12; $m++) {
            // Create a date for the given month and format it to the full month name (e.g., "January").
            $gregorianMonths[] = Carbon::create(null, $m, 1)->format('F');
        }

        $chartSeries = [
            ['name' => 'Total Working Hours', 'data' => $monthlyHours]
        ];

        return view('employees.reports.yearly', [
            'employee' => $employee,
            'chartSeries' => $chartSeries,
            'chartCategories' => $gregorianMonths, // Pass Gregorian months to the view
            'targetDate' => $targetDate,
        ]);
    }

    // new public method for the D3 report page
    public function showMonthlyD3Report(Request $request, Employee $employee, $year = null, $month = null)
    {
        if ($year && $month) {
            $targetDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        } else {
            $targetDate = Carbon::now()->startOfDay();
        }

        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('timestamp', [$startOfMonth, $endOfMonth])
            ->orderBy('timestamp', 'asc')
            ->get();

        $d3ChartData = $this->processAttendanceForD3Chart($attendances);

        // --- NEW: Create a list of all Gregorian months ---
        $allGregorianMonths = [];
        for ($m = 1; $m <= 12; $m++) {
            // Create a Carbon instance for each month to get its name (e.g., "January")
            $allGregorianMonths[$m] = Carbon::create(null, $m, 1)->format('F');
        }

        $currentYear = $targetDate->year;
        $yearRange = range($currentYear - 5, $currentYear + 2);

        return view('employees.reports.monthly_d3', [
            'employee' => $employee,
            'd3ChartData' => $d3ChartData,
            'targetDate' => $targetDate,
            'officeHours' => ['start' => 6.0, 'end' => 13.0],
            'allMonths' => $allGregorianMonths, // Changed variable name for clarity
            'yearRange' => $yearRange,
        ]);
    }

    // private helper method to format data for D3
    private function processAttendanceForD3Chart($attendances)
    {
        $eventsByDate = $attendances->groupBy(function ($event) {
            return Carbon::parse($event->timestamp)->format('Y-m-d');
        });

        $finalData = [];

        foreach ($eventsByDate as $date => $dailyEvents) {
            $dayOfMonth = Carbon::parse($date)->day;

            $dayData = [
                'day' => $dayOfMonth,
                'intervals' => [],
                'entries' => [],
                'exits' => [],
            ];

            $lastEntry = null;

            foreach ($dailyEvents as $event) {
                $eventTime = Carbon::parse($event->timestamp);
                $decimalHour = $eventTime->hour + ($eventTime->minute / 60);

                if ($event->event_type === 'entry') {
                    if ($lastEntry) {
                        $lastEntryTime = Carbon::parse($lastEntry->timestamp);
                        $dayData['entries'][] = $lastEntryTime->hour + ($lastEntryTime->minute / 60);
                    }
                    $lastEntry = $event;
                }

                if ($event->event_type === 'exit') {
                    if ($lastEntry) {
                        $entryTime = Carbon::parse($lastEntry->timestamp);
                        $startHour = $entryTime->hour + ($entryTime->minute / 60);

                        // --- THIS IS THE KEY CHANGE ---
                        // 1. Add the interval for the black line.
                        $dayData['intervals'][] = ['start' => $startHour, 'end' => $decimalHour];

                        // 2. ALSO add the start and end points to the dot arrays.
                        $dayData['entries'][] = $startHour; // For the green dot
                        $dayData['exits'][] = $decimalHour;   // For the red dot

                        $lastEntry = null;
                    } else {
                        $dayData['exits'][] = $decimalHour;
                    }
                }
            }

            if ($lastEntry) {
                $lastEntryTime = Carbon::parse($lastEntry->timestamp);
                $dayData['entries'][] = $lastEntryTime->hour + ($lastEntryTime->minute / 60);
            }

            $finalData[] = $dayData;
        }

        return $finalData;
    }
}
