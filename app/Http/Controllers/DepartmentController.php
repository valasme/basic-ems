<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Throwable;

class DepartmentController extends Controller
{
    /**
     * Available filter modes for department listing.
     *
     * @var list<string>
     */
    private const FILTERS = [
        'name_alpha',
        'name_reverse',
        'description_alpha',
        'description_reverse',
        'created_newest',
        'created_oldest',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Department::class);

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

        /** @var LengthAwarePaginator<Department> $departments */
        $departments = $this->emptyDepartmentsPaginator($request);

        try {
            $departments = $this->buildFilteredDepartmentsPaginator($request, $search, $filter);
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash('error', 'Unable to apply the selected filter. Showing default ordering instead.');
            $filter = 'name_alpha';

            try {
                $departments = $this->buildFilteredDepartmentsPaginator($request, $search, $filter);
            } catch (Throwable $fallbackException) {
                report($fallbackException);

                $request->session()->flash('error', 'Unable to load departments right now. Please try again.');
            }
        }

        return view('departments.index', [
            'departments' => $departments,
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

        if (! in_array($rawFilter, self::FILTERS, true)) {
            return ['name_alpha', 'Invalid filter selected. Showing default ordering.'];
        }

        return [$rawFilter, null];
    }

    /**
     * Build paginated departments for the selected filter.
     *
     * @return LengthAwarePaginator<Department>
     */
    private function buildFilteredDepartmentsPaginator(Request $request, ?string $search, string $filter): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Department> $departments */
        $departments = $this->applyFilter(
            $request->user()
                ->departments()
                ->withCount('employees')
                ->search($search),
            $filter
        )
            ->paginate(25)
            ->withQueryString();

        return $departments;
    }

    /**
     * Get an empty paginator for failure fallback states.
     *
     * @return LengthAwarePaginator<Department>
     */
    private function emptyDepartmentsPaginator(Request $request): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Department> $paginator */
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
     * @param  Builder<Department>|HasMany<Department, User>  $query
     * @return Builder<Department>|HasMany<Department, User>
     */
    private function applyFilter(Builder|HasMany $query, string $filter): Builder|HasMany
    {
        return match ($filter) {
            'name_reverse' => $query->orderByDesc('name'),
            'description_alpha' => $query
                ->orderByRaw('CASE WHEN description IS NULL OR description = "" THEN 1 ELSE 0 END')
                ->orderBy('description')
                ->orderBy('name'),
            'description_reverse' => $query
                ->orderByRaw('CASE WHEN description IS NULL OR description = "" THEN 1 ELSE 0 END')
                ->orderByDesc('description')
                ->orderBy('name'),
            'created_newest' => $query->latest('created_at')->latest('id'),
            'created_oldest' => $query->orderBy('created_at')->orderBy('id'),
            default => $query->orderBy('name'),
        };
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Department::class);

        return view('departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        try {
            $request->user()->departments()->create($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to create department right now. Please try again.');
        }

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Department $department): View
    {
        $this->authorize('view', $department);

        try {
            $department->loadCount('employees');
        } catch (Throwable $exception) {
            report($exception);

            $department->setAttribute('employees_count', 0);
            $request->session()->flash('error', 'Unable to load employee count right now.');
        }

        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department): View|RedirectResponse
    {
        $this->authorize('update', $department);

        try {
            $department->refresh();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('departments.index')
                ->with('error', 'Unable to load department for editing. Please try again.');
        }

        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        try {
            $department->update($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update department right now. Please try again.');
        }

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $name = $department->name;

        try {
            $department->delete();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('departments.index')
                ->with('error', 'Unable to delete department right now. Please try again.');
        }

        return redirect()
            ->route('departments.index')
            ->with('success', "Department '{$name}' deleted successfully.");
    }
}
