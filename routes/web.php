<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// Custom show route: /employees/{employee}/show
Route::get('employees/{employee}/show', [EmployeeController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('employees.show');

// Resource routes except 'show'
Route::resource('employees', EmployeeController::class)
    ->except(['show'])
    ->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
