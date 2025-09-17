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

    public function showMonthlyReport(Employee $employee)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Fetch all attendance records for this employee for the current month, sorted by time
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('timestamp', [$startOfMonth, $endOfMonth])
            ->orderBy('timestamp', 'asc')
            ->get();

        $chartData = $this->processAttendanceForChart($attendances);

        return view('employees.reports.monthly', [
            'employee' => $employee,
            'chartSeries' => $chartData['series'],
            'chartCategories' => $chartData['categories'],
        ]);
    }

    /**
     * Processes raw attendance records into a format suitable for the ApexCharts timeline.
     * THIS IS THE FINAL, CORRECTED VERSION.
     */
    private function processAttendanceForChart($attendances)
    {
        $seriesData = [
            'bars' => [],
            'entries' => [],
            'exits' => [],
        ];
        $categories = [];
        $processedDates = [];
        $lastEntry = null;

        foreach ($attendances as $event) {
            $eventTime = Carbon::parse($event->timestamp);

            // --- FIX #2: Create a combined Jalali and Gregorian date label ---
            $jalaliDate = Jalalian::fromCarbon($eventTime)->format('Y/m/d');
            $gregorianDate = $eventTime->format('Y-m-d');
            $combinedDateLabel = $this->convertPersianNumbersToEnglish($jalaliDate) . "  ($gregorianDate)";

            if (!in_array($combinedDateLabel, $processedDates)) {
                $categories[] = $combinedDateLabel;
                $processedDates[] = $combinedDateLabel;
            }

            // --- FIX #1: Force the timestamp to be based on the year 1970 ---
            $timeValue = Carbon::create(1970, 1, 1, $eventTime->hour, $eventTime->minute, $eventTime->second);

            if ($event->event_type === 'entry') {
                if ($lastEntry) {
                    $lastEntryTime = Carbon::parse($lastEntry->timestamp);
                    $entryTimeValue = Carbon::create(1970, 1, 1, $lastEntryTime->hour, $lastEntryTime->minute, $lastEntryTime->second);

                    $jalaliEntryDate = Jalalian::fromCarbon($lastEntryTime)->format('Y/m/d');
                    $gregorianEntryDate = $lastEntryTime->format('Y-m-d');
                    $combinedEntryLabel = $this->convertPersianNumbersToEnglish($jalaliEntryDate) . "  ($gregorianEntryDate)";

                    $seriesData['entries'][] = [
                        'x' => $combinedEntryLabel,
                        'y' => [
                            $entryTimeValue->clone()->timestamp * 1000,
                            $entryTimeValue->clone()->addSecond()->timestamp * 1000
                        ],
                    ];
                }
                $lastEntry = $event;
            }

            if ($event->event_type === 'exit') {
                if ($lastEntry) {
                    $entryTime = Carbon::parse($lastEntry->timestamp);
                    $entryTimeValue = Carbon::create(1970, 1, 1, $entryTime->hour, $entryTime->minute, $entryTime->second);

                    $seriesData['bars'][] = [
                        'x' => $combinedDateLabel,
                        'y' => [
                            $entryTimeValue->timestamp * 1000,
                            $timeValue->timestamp * 1000
                        ],
                    ];
                    $lastEntry = null;
                } else {
                    $seriesData['exits'][] = [
                        'x' => $combinedDateLabel,
                        'y' => [
                            $timeValue->clone()->timestamp * 1000,
                            $timeValue->clone()->addSecond()->timestamp * 1000
                        ],
                    ];
                }
            }
        }

        if ($lastEntry) {
            $lastEntryTime = Carbon::parse($lastEntry->timestamp);
            $entryTimeValue = Carbon::create(1970, 1, 1, $lastEntryTime->hour, $lastEntryTime->minute, $lastEntryTime->second);

            $jalaliEntryDate = Jalalian::fromCarbon($lastEntryTime)->format('Y/m/d');
            $gregorianEntryDate = $lastEntryTime->format('Y-m-d');
            $combinedEntryLabel = $this->convertPersianNumbersToEnglish($jalaliEntryDate) . "  ($gregorianEntryDate)";

            $seriesData['entries'][] = [
                'x' => $combinedEntryLabel,
                'y' => [
                    $entryTimeValue->clone()->timestamp * 1000,
                    $entryTimeValue->clone()->addSecond()->timestamp * 1000
                ],
            ];
        }

        $finalSeries = [
            ['name' => 'ساعات کاری', 'data' => $seriesData['bars']],
            ['name' => 'ورود', 'data' => $seriesData['entries']],
            ['name' => 'خروج', 'data' => $seriesData['exits']],
        ];

        return ['series' => $finalSeries, 'categories' => array_unique($categories)];
    }

    private function convertPersianNumbersToEnglish($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian, $english, $string);
    }
}
