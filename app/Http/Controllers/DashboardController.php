<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $user->loadCount(['employees', 'tasks', 'notes']);

        $urgentTasks = Task::query()
            ->select(['id', 'user_id', 'employee_id', 'title', 'priority', 'due_date', 'created_at'])
            ->ownedBy($user)
            ->urgent()
            ->with('employee:id,first_name,last_name')
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard', [
            'employeesCount' => $user->employees_count,
            'tasksCount' => $user->tasks_count,
            'notesCount' => $user->notes_count,
            'urgentTasks' => $urgentTasks,
        ]);
    }
}
