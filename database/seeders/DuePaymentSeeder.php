<?php

namespace Database\Seeders;

use App\Models\DuePayment;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DuePaymentSeeder extends Seeder
{
    /**
     * The number of due payments to create per user.
     */
    private const PAYMENTS_PER_USER = 15;

    /**
     * The number of users to seed due payments for.
     */
    private const USER_LIMIT = 5;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()
            ->select(['id'])
            ->orderBy('id')
            ->limit(self::USER_LIMIT)
            ->withCount('duePayments')
            ->with(['employees:id,user_id,pay_amount'])
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $users->each(function (User $user): void {
            $employees = $user->employees;

            if ($employees->isEmpty()) {
                $employee = Employee::factory()->forUser($user)->create();
                $employees = collect([$employee]);
            }

            $paymentsToCreate = max(0, self::PAYMENTS_PER_USER - (int) $user->due_payments_count);

            if ($paymentsToCreate === 0) {
                return;
            }

            DuePayment::factory()
                ->count($paymentsToCreate)
                ->state(function (array $attributes) use ($employees): array {
                    $employee = $employees->random();
                    $payDate = Carbon::today()->addDays(fake()->numberBetween(-3, 14));

                    return [
                        'employee_id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'amount' => $employee->pay_amount ?? fake()->randomFloat(2, 500, 5000),
                        'pay_date' => $payDate->format('Y-m-d'),
                    ];
                })
                ->create();
        });
    }
}
