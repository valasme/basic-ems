<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Throwable;

class EmployeeController extends Controller
{
    /**
     * Available filter modes for employee listing.
     *
     * @var list<string>
     */
    private const FILTERS = [
        'name_alpha',
        'name_reverse',
        'email_alpha',
        'email_reverse',
        'department_alpha',
        'department_reverse',
        'job_title_alpha',
        'job_title_reverse',
        'created_newest',
        'created_oldest',
        'salary_highest',
        'salary_lowest',
    ];

    /**
     * Backward-compatible aliases for previously used filter keys.
     *
     * @var array<string, string>
     */
    private const FILTER_ALIASES = [
        'created_date' => 'created_newest',
        'salary_calculated' => 'salary_highest',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        /** @var string|null $search */
        $search = $request->query('search');
        /** @var string|null $rawFilter */
        $rawFilter = is_string($request->query('filter'))
            ? $request->query('filter')
            : null;

        [$filter, $validationError] = $this->resolveFilter($rawFilter);

        if ($validationError !== null) {
            $request->session()->flash('error', $validationError);
        }

        /** @var LengthAwarePaginator<Employee> $employees */
        $employees = $this->emptyEmployeesPaginator($request);

        try {
            $employees = $this->buildFilteredEmployeesPaginator($request, $search, $filter);
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash('error', 'Unable to apply the selected filter. Showing default ordering instead.');
            $filter = 'name_alpha';

            try {
                $employees = $this->buildFilteredEmployeesPaginator($request, $search, $filter);
            } catch (Throwable $fallbackException) {
                report($fallbackException);

                $request->session()->flash('error', 'Unable to load employees right now. Please try again.');
            }
        }

        return view('employees.index', [
            'employees' => $employees,
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
            return ['name_alpha', null];
        }

        $candidate = self::FILTER_ALIASES[$rawFilter] ?? $rawFilter;

        if (! in_array($candidate, self::FILTERS, true)) {
            return ['name_alpha', 'Invalid filter selected. Showing default ordering.'];
        }

        return [$candidate, null];
    }

    /**
     * Build paginated employees for the selected filter.
     *
     * @return LengthAwarePaginator<Employee>
     */
    private function buildFilteredEmployeesPaginator(Request $request, ?string $search, string $filter): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Employee> $employees */
        $employees = $this->applyFilter(
            $request->user()
                ->employees()
                ->search($search),
            $filter
        )
            ->paginate(25)
            ->withQueryString();

        return $employees;
    }

    /**
     * Get an empty paginator for failure fallback states.
     *
     * @return LengthAwarePaginator<Employee>
     */
    private function emptyEmployeesPaginator(Request $request): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Employee> $paginator */
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
     * @param  Builder<Employee>|HasMany<Employee, $this>  $query
     * @return Builder<Employee>|HasMany<Employee, $this>
     */
    private function applyFilter(Builder|HasMany $query, string $filter): Builder|HasMany
    {
        return match ($filter) {
            'name_reverse' => $query->orderByDesc('first_name')->orderByDesc('last_name'),
            'email_alpha' => $query->orderBy('email')->orderBy('first_name')->orderBy('last_name'),
            'email_reverse' => $query->orderByDesc('email')->orderByDesc('first_name')->orderByDesc('last_name'),
            'department_alpha' => $query
                ->orderByRaw('CASE WHEN department IS NULL OR department = "" THEN 1 ELSE 0 END')
                ->orderBy('department')
                ->orderBy('first_name')
                ->orderBy('last_name'),
            'department_reverse' => $query
                ->orderByRaw('CASE WHEN department IS NULL OR department = "" THEN 1 ELSE 0 END')
                ->orderByDesc('department')
                ->orderBy('first_name')
                ->orderBy('last_name'),
            'job_title_alpha' => $query
                ->orderByRaw('CASE WHEN job_title IS NULL OR job_title = "" THEN 1 ELSE 0 END')
                ->orderBy('job_title')
                ->orderBy('first_name')
                ->orderBy('last_name'),
            'job_title_reverse' => $query
                ->orderByRaw('CASE WHEN job_title IS NULL OR job_title = "" THEN 1 ELSE 0 END')
                ->orderByDesc('job_title')
                ->orderBy('first_name')
                ->orderBy('last_name'),
            'created_newest' => $query->latest('created_at')->latest('id'),
            'created_oldest' => $query->orderBy('created_at')->orderBy('id'),
            'salary_highest' => $query
                ->orderByRaw('COALESCE(pay_salary, (COALESCE(pay_amount, 0) * 12)) DESC')
                ->orderBy('first_name')
                ->orderBy('last_name'),
            'salary_lowest' => $query
                ->orderByRaw('COALESCE(pay_salary, (COALESCE(pay_amount, 0) * 12)) ASC')
                ->orderBy('first_name')
                ->orderBy('last_name'),
            default => $query->orderByName(),
        };
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Employee::class);

        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        try {
            $request->user()->employees()->create($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to create employee right now. Please try again.');
        }

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);

        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        try {
            $employee->update($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update employee right now. Please try again.');
        }

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        try {
            $employee->delete();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('employees.index')
                ->with('error', 'Unable to delete employee right now. Please try again.');
        }

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
