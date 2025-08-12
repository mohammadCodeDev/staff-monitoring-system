<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\GroupController;


Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

//the settings page
Route::get('/settings', function () {
    return view('settings');
})->middleware(['auth'])->name('settings');

//POST route for updating the locale
Route::post('/settings/locale', [SettingsController::class, 'updateLocale'])->name('settings.locale.update');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes for managing employees, accessible only by authenticated users.
Route::middleware(['auth', 'role:Roles.System Admin'])->group(function () {
    Route::resource('employees', EmployeeController::class);

    // route to handle the deactivation request
    Route::patch('employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');

    // route to handle the reactivation request
    Route::patch('employees/{employee}/reactivate', [EmployeeController::class, 'reactivate'])->name('employees.reactivate');

    // Add the new resource route for departments here
    Route::resource('departments', DepartmentController::class);

    Route::resource('groups', GroupController::class);
});

Route::middleware(['auth', 'role:Roles.System Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.updateRole');
});

// Routes for creating attendance records
Route::get('/attendances/create', [AttendanceController::class, 'create'])
    ->middleware(['auth', 'role:Roles.System Admin,Roles.Guard'])
    ->name('attendances.create');

Route::post('/attendances', [AttendanceController::class, 'store'])
    ->middleware(['auth', 'role:Roles.System Admin,Roles.Guard'])
    ->name('attendances.store');

// Route for the attendance monitoring page
Route::get('/attendances', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'role:Roles.System Admin,Roles.System Observer,Roles.University President,Roles.Faculty Head,Roles.Group Manager'])
    ->name('attendances.index');

require __DIR__ . '/auth.php';
