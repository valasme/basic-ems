<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DuePaymentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('employees', EmployeeController::class);
    Route::resource('tasks', TaskController::class);
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('due-payments', [DuePaymentController::class, 'index'])->name('due-payments.index');
    Route::resource('notes', NoteController::class);
});

require __DIR__.'/settings.php';
