<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        /** @var string|null $search */
        $search = $request->query('search');
        $user = $request->user();

        $employees = collect();

        try {
            $employees = $user
                ->employees()
                ->select(['id', 'user_id', 'department_id', 'first_name', 'last_name', 'work_in', 'work_out', 'job_title'])
                ->with('department:id,name')
                ->search($search)
                ->get()
                ->sortBy(function (Employee $employee): string {
                    return $employee->work_in ?? '99:99';
                })
                ->values();
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash('error', 'Unable to load attendance right now. Please try again.');
        }

        return view('attendance.index', [
            'employees' => $employees,
            'search' => $search,
        ]);
    }
}
