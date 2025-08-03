<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
Route::middleware(['auth', 'role:System Admin'])->group(function () {
    Route::resource('employees', EmployeeController::class);

    // route to handle the deactivation request
    Route::patch('employees/{employee}/deactivate', [EmployeeController::class, 'deactivate'])->name('employees.deactivate');

    // route to handle the reactivation request
    Route::patch('employees/{employee}/reactivate', [EmployeeController::class, 'reactivate'])->name('employees.reactivate');
});

require __DIR__ . '/auth.php';
