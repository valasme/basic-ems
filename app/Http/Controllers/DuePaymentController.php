<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDuePaymentRequest;
use App\Http\Requests\UpdateDuePaymentRequest;
use App\Models\DuePayment;
use App\Models\Employee;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DuePaymentController extends Controller
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

        /** @var Collection<int, Employee> $employees */
        $employees = $user
            ->employees()
            ->select(['id', 'user_id', 'first_name', 'last_name', 'pay_day', 'pay_amount', 'job_title'])
            ->withPayDay()
            ->search($search)
            ->get()
            ->sortBy(fn (Employee $employee) => $employee->days_until_pay ?? PHP_INT_MAX)
            ->values();

        return view('duepayments.index', [
            'employees' => $employees,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', DuePayment::class);
        $user = $request->user();

        /** @var Collection<int, Employee> $employees */
        $employees = $user
            ->employees()
            ->select(['id', 'user_id', 'first_name', 'last_name', 'pay_day', 'pay_amount'])
            ->orderByName()
            ->get();

        return view('duepayments.create', [
            'employees' => $employees,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDuePaymentRequest $request): RedirectResponse
    {
        $request->user()
            ->duePayments()
            ->create($request->validated());

        return redirect()
            ->route('due-payments.index')
            ->with('success', 'Due payment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DuePayment $duePayment): View
    {
        $this->authorize('view', $duePayment);

        $duePayment->loadMissing('employee:id,user_id,first_name,last_name');

        return view('duepayments.show', [
            'duePayment' => $duePayment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, DuePayment $duePayment): View
    {
        $this->authorize('update', $duePayment);
        $user = $request->user();

        /** @var Collection<int, Employee> $employees */
        $employees = $user
            ->employees()
            ->select(['id', 'user_id', 'first_name', 'last_name', 'pay_day', 'pay_amount'])
            ->orderByName()
            ->get();

        return view('duepayments.edit', [
            'duePayment' => $duePayment,
            'employees' => $employees,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDuePaymentRequest $request, DuePayment $duePayment): RedirectResponse
    {
        $duePayment->update($request->validated());

        return redirect()
            ->route('due-payments.index')
            ->with('success', 'Due payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DuePayment $duePayment): RedirectResponse
    {
        $this->authorize('delete', $duePayment);

        $duePayment->delete();

        return redirect()
            ->route('due-payments.index')
            ->with('success', 'Due payment deleted successfully.');
    }
}
