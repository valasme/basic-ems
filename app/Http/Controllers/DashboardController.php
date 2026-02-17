<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        try {
            $user->loadCount(['employees', 'tasks', 'notes']);

            [$priorityTasks, $isShowingFallbackTasks] = $this->resolvePriorityTasks($user->id);
            $duePaymentsPreview = $this->resolveDuePaymentsPreview($user->id);

            return view('dashboard', [
                'employeesCount' => $user->employees_count,
                'tasksCount' => $user->tasks_count,
                'notesCount' => $user->notes_count,
                'priorityTasks' => $priorityTasks,
                'isShowingFallbackTasks' => $isShowingFallbackTasks,
                'duePaymentsPreview' => $duePaymentsPreview,
                'dashboardLoadError' => false,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return view('dashboard', [
                'employeesCount' => 0,
                'tasksCount' => 0,
                'notesCount' => 0,
                'priorityTasks' => collect(),
                'isShowingFallbackTasks' => false,
                'duePaymentsPreview' => collect(),
                'dashboardLoadError' => true,
            ]);
        }
    }

    /**
     * Build the base query used by dashboard priority task sections.
     *
     * @return Builder<Task>
     */
    private function dashboardTaskQuery(int $userId): Builder
    {
        return Task::query()
            ->select(['id', 'user_id', 'employee_id', 'title', 'priority', 'due_date', 'created_at'])
            ->where('user_id', $userId)
            ->with('employee:id,first_name,last_name');
    }

    /**
     * Resolve priority tasks and whether fallback mode is active.
     *
     * @return array{Collection<int, Task>, bool}
     */
    private function resolvePriorityTasks(int $userId): array
    {
        $taskQuery = $this->dashboardTaskQuery($userId);

        $urgentTasks = (clone $taskQuery)
            ->urgent()
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get();

        if ($urgentTasks->isNotEmpty()) {
            return [$urgentTasks, false];
        }

        $fallbackTasks = (clone $taskQuery)
            ->where('priority', '!=', 'urgent')
            ->orderByRaw("case priority when 'high' then 1 when 'medium' then 2 when 'low' then 3 when 'none' then 4 else 5 end")
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get();

        return [$fallbackTasks, true];
    }

    /**
     * Resolve due payments preview sorted by nearest pay date.
     *
     * @return Collection<int, Employee>
     */
    private function resolveDuePaymentsPreview(int $userId): Collection
    {
        return Employee::query()
            ->select(['id', 'user_id', 'first_name', 'last_name', 'pay_day', 'pay_amount'])
            ->where('user_id', $userId)
            ->withPayDay()
            ->get()
            ->sortBy(fn (Employee $employee): int => $employee->days_until_pay ?? PHP_INT_MAX)
            ->take(7)
            ->values();
    }
}
