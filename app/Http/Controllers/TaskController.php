<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Illuminate\Support\Collection;
use Throwable;

class TaskController extends Controller
{
    /**
     * Available filter modes for task listing.
     *
     * @var list<string>
     */
    private const FILTERS = [
        'priority_only',
        'priority_status',
        'title_alpha',
        'title_reverse',
        'employee_alpha',
        'employee_reverse',
        'status_workflow',
        'status_reverse',
        'due_date_soonest',
        'due_date_latest',
        'created_newest',
    ];

    /**
     * Backward-compatible aliases for simple and legacy filter values.
     *
     * @var array<string, string>
     */
    private const FILTER_ALIASES = [
        'priority' => 'priority_only',
        'title' => 'title_alpha',
        'employee' => 'employee_alpha',
        'status' => 'status_workflow',
        'due_date' => 'due_date_soonest',
        'created_date' => 'created_newest',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        /** @var string|null $search */
        $search = $request->query('search');
        $user = $request->user();
        /** @var string|null $rawFilter */
        $rawFilter = is_string($request->query('filter'))
            ? $request->query('filter')
            : null;

        [$filter, $validationError] = $this->resolveFilter($rawFilter);

        if ($validationError !== null) {
            $request->session()->flash('error', $validationError);
        }

        /** @var LengthAwarePaginator<Task> $tasks */
        $tasks = $this->emptyTasksPaginator($request);

        try {
            $tasks = $this->buildFilteredTasksPaginator($user->id, $search, $filter, $request);
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash('error', 'Unable to apply the selected filter. Showing default ordering instead.');
            $filter = 'priority_status';

            try {
                $tasks = $this->buildFilteredTasksPaginator($user->id, $search, $filter, $request);
            } catch (Throwable $fallbackException) {
                report($fallbackException);

                $request->session()->flash('error', 'Unable to load tasks right now. Please try again.');
            }
        }

        return view('tasks.index', [
            'tasks' => $tasks,
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    /**
     * Resolve filter value from request input.
     *
     * @return array{0: string, 1: string|null}
     */
    private function resolveFilter(?string $rawFilter): array
    {
        if ($rawFilter === null) {
            return ['priority_status', null];
        }

        $candidate = self::FILTER_ALIASES[$rawFilter] ?? $rawFilter;

        if (! in_array($candidate, self::FILTERS, true)) {
            return ['priority_status', 'Invalid filter selected. Showing default ordering.'];
        }

        return [$candidate, null];
    }

    /**
     * Build paginated tasks for the selected filter.
     *
     * @return LengthAwarePaginator<Task>
     */
    private function buildFilteredTasksPaginator(int $userId, ?string $search, string $filter, Request $request): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Task> $tasks */
        $tasks = $this->applyFilter(
            Task::query()
                ->select(['tasks.id', 'tasks.employee_id', 'tasks.title', 'tasks.status', 'tasks.priority', 'tasks.due_date', 'tasks.created_at'])
                ->with(['employee:id,user_id,first_name,last_name'])
                ->where('tasks.user_id', $userId)
                ->search($search),
            $filter
        )
            ->paginate(25)
            ->withQueryString();

        return $tasks;
    }

    /**
     * Get an empty paginator for failure fallback states.
     *
     * @return LengthAwarePaginator<Task>
     */
    private function emptyTasksPaginator(Request $request): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Task> $paginator */
        $paginator = new BaseLengthAwarePaginator(
            collect(),
            0,
            25,
            $request->integer('page', 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return $paginator;
    }

    /**
     * Apply ordering strategy based on selected filter mode.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    private function applyFilter(Builder $query, string $filter): Builder
    {
        $priorityOrder = "CASE
            WHEN tasks.priority = 'urgent' THEN 1
            WHEN tasks.priority = 'high' THEN 2
            WHEN tasks.priority = 'medium' THEN 3
            WHEN tasks.priority = 'low' THEN 4
            ELSE 5
        END";

        $statusOrder = "CASE
            WHEN tasks.status = 'pending' THEN 1
            WHEN tasks.status = 'in_progress' THEN 2
            ELSE 3
        END";

        $dueDateNullLast = 'CASE WHEN tasks.due_date IS NULL THEN 1 ELSE 0 END';

        return match ($filter) {
            'priority_only' => $query
                ->orderByRaw($priorityOrder)
                ->orderByRaw($dueDateNullLast)
                ->orderBy('tasks.due_date')
                ->latest('tasks.created_at'),
            'title_alpha' => $query->orderBy('tasks.title')->latest('tasks.created_at'),
            'title_reverse' => $query->orderByDesc('tasks.title')->latest('tasks.created_at'),
            'employee_alpha' => $query
                ->leftJoin('employees as sort_employee', 'sort_employee.id', '=', 'tasks.employee_id')
                ->orderByRaw('CASE WHEN sort_employee.id IS NULL THEN 1 ELSE 0 END')
                ->orderBy('sort_employee.first_name')
                ->orderBy('sort_employee.last_name')
                ->orderBy('tasks.title')
                ->select(['tasks.id', 'tasks.employee_id', 'tasks.title', 'tasks.status', 'tasks.priority', 'tasks.due_date', 'tasks.created_at']),
            'employee_reverse' => $query
                ->leftJoin('employees as sort_employee', 'sort_employee.id', '=', 'tasks.employee_id')
                ->orderByRaw('CASE WHEN sort_employee.id IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('sort_employee.first_name')
                ->orderByDesc('sort_employee.last_name')
                ->orderBy('tasks.title')
                ->select(['tasks.id', 'tasks.employee_id', 'tasks.title', 'tasks.status', 'tasks.priority', 'tasks.due_date', 'tasks.created_at']),
            'status_workflow' => $query
                ->orderByRaw($statusOrder)
                ->orderByRaw($priorityOrder)
                ->orderBy('tasks.title')
                ->latest('tasks.created_at'),
            'status_reverse' => $query
                ->orderByRaw($statusOrder.' DESC')
                ->orderByRaw($priorityOrder)
                ->orderBy('tasks.title')
                ->latest('tasks.created_at'),
            'due_date_soonest' => $query
                ->orderByRaw($dueDateNullLast)
                ->orderBy('tasks.due_date')
                ->latest('tasks.created_at'),
            'due_date_latest' => $query
                ->orderByRaw($dueDateNullLast)
                ->orderByDesc('tasks.due_date')
                ->latest('tasks.created_at'),
            'created_newest' => $query->latest('tasks.created_at')->latest('tasks.id'),
            default => $query
                ->orderByRaw($priorityOrder)
                ->orderByRaw($statusOrder)
                ->orderByRaw($dueDateNullLast)
                ->orderBy('tasks.due_date')
                ->latest('tasks.created_at'),
        };
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Task::class);
        $user = $request->user();

        /** @var Collection<int, Employee> $employees */
        $employees = $user
            ->employees()
            ->select(['id', 'user_id', 'first_name', 'last_name'])
            ->orderByName()
            ->get();

        return view('tasks.create', [
            'employees' => $employees,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        try {
            $request->user()
                ->tasks()
                ->create($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to create task right now. Please try again.');
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->loadMissing('employee:id,user_id,first_name,last_name');

        return view('tasks.show', [
            'task' => $task,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Task $task): View
    {
        $this->authorize('update', $task);
        $user = $request->user();

        /** @var Collection<int, Employee> $employees */
        $employees = $user
            ->employees()
            ->select(['id', 'user_id', 'first_name', 'last_name'])
            ->orderByName()
            ->get();

        return view('tasks.edit', [
            'task' => $task,
            'employees' => $employees,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        try {
            $task->update($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update task right now. Please try again.');
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        try {
            $task->delete();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('tasks.index')
                ->with('error', 'Unable to delete task right now. Please try again.');
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}
