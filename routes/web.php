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
Route::middleware('auth')->group(function () {
    Route::resource('employees', EmployeeController::class);
});

require __DIR__ . '/auth.php';
