<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', function (Request $request) {
    $user = $request->user();

    return view('dashboard', [
        'employeesCount' => $user->employees()->count(),
        'tasksCount' => $user->tasks()->count(),
        'notesCount' => $user->notes()->count(),
    ]);
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('employees', EmployeeController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('notes', NoteController::class);
});

require __DIR__.'/settings.php';
