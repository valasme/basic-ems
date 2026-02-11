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

        $taskQuery = Task::query()
            ->select(['id', 'user_id', 'employee_id', 'title', 'priority', 'due_date', 'created_at'])
            ->ownedBy($user)
            ->with('employee:id,first_name,last_name');

        $urgentTasks = (clone $taskQuery)
            ->urgent()
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get();

        $isShowingFallbackTasks = false;
        $priorityTasks = $urgentTasks;

        if ($urgentTasks->isEmpty()) {
            $priorityTasks = (clone $taskQuery)
                ->where('priority', '!=', 'urgent')
                ->orderByRaw("case priority when 'high' then 1 when 'medium' then 2 when 'low' then 3 when 'none' then 4 else 5 end")
                ->orderBy('due_date')
                ->orderByDesc('created_at')
                ->limit(7)
                ->get();
            $isShowingFallbackTasks = true;
        }

        return view('dashboard', [
            'employeesCount' => $user->employees_count,
            'tasksCount' => $user->tasks_count,
            'notesCount' => $user->notes_count,
            'priorityTasks' => $priorityTasks,
            'isShowingFallbackTasks' => $isShowingFallbackTasks,
        ]);
    }
}
