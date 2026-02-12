<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Employee;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Throwable;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        /** @var string|null $search */
        $search = $request->query('search');
        $user = $request->user();

        /** @var LengthAwarePaginator<Task> $tasks */
        $tasks = Task::query()
            ->select(['id', 'employee_id', 'title', 'status', 'priority', 'due_date', 'created_at'])
            ->with(['employee:id,user_id,first_name,last_name'])
            ->ownedBy($user)
            ->search($search)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'search' => $search,
        ]);
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
