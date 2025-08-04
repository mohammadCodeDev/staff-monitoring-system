<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

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
}
