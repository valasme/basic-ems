<?php

namespace App\Http\Controllers;

use App\Models\DuePayment;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Throwable;

class DuePaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DuePayment::class);

        /** @var string|null $search */
        $search = $request->query('search');
        $user = $request->user();

        $employees = collect();

        try {
            $employees = $user
                ->employees()
                ->select(['id', 'user_id', 'first_name', 'last_name', 'pay_day', 'pay_amount', 'job_title'])
                ->withPayDay()
                ->search($search)
                ->get()
                ->sortBy(fn (Employee $employee): int => $employee->days_until_pay ?? PHP_INT_MAX)
                ->values();
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash('error', 'Unable to load due payments right now. Please try again.');
        }

        return view('duepayments.index', [
            'employees' => $employees,
            'search' => $search,
        ]);
    }
}
