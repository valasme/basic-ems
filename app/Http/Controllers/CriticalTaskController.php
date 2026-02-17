<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Throwable;

class CriticalTaskController extends Controller
{
    /**
     * Available filter modes for critical task ranking.
     *
     * @var list<string>
     */
    private const FILTERS = [
        'time_priority',
        'priority_only',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        /** @var string|null $search */
        $search = $request->query('search');
        /** @var string|null $rawFilter */
        $rawFilter = is_string($request->query('filter'))
            ? $request->query('filter')
            : null;

        $filter = in_array($rawFilter, self::FILTERS, true)
            ? $rawFilter
            : 'time_priority';

        if ($rawFilter !== null && $rawFilter !== $filter) {
            $request->session()->flash('error', 'Invalid filter selected. Showing default ranking.');
        }

        $tasks = collect();

        try {
            $tasks = $this->applyFilter(
                Task::query()
                    ->select(['id', 'user_id', 'employee_id', 'title', 'status', 'priority', 'due_date', 'created_at'])
                    ->with(['employee:id,user_id,first_name,last_name'])
                    ->ownedBy($request->user())
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->search($search),
                $filter
            )->get();
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash('error', 'Unable to load critical tasks right now. Please try again.');
        }

        return view('criticaltasks.index', [
            'tasks' => $tasks,
            'search' => $search,
            'filter' => $filter,
        ]);
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
            WHEN priority = 'urgent' THEN 1
            WHEN priority = 'high' THEN 2
            WHEN priority = 'medium' THEN 3
            WHEN priority = 'low' THEN 4
            ELSE 5
        END";

        if ($filter === 'priority_only') {
            return $query
                ->orderByRaw($priorityOrder)
                ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('due_date')
                ->latest('created_at');
        }

        return $query
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderByRaw($priorityOrder)
            ->latest('created_at');
    }
}
