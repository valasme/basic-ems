<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * The number of employees to create per user.
     */
    private const EMPLOYEES_PER_USER = 25;

    /**
     * The number of users to seed employees for.
     */
    private const USER_LIMIT = 5;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()
            ->orderBy('id')
            ->limit(self::USER_LIMIT)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $users->each(function (User $user): void {
            $existingEmployeesCount = $user->employees()->count();
            $employeesToCreate = max(0, self::EMPLOYEES_PER_USER - $existingEmployeesCount);
            $departmentIds = $user->departments()->pluck('id');

            if ($employeesToCreate === 0) {
                return;
            }

            $employeeFactory = Employee::factory()
                ->count($employeesToCreate)
                ->forUser($user);

            if ($departmentIds->isNotEmpty()) {
                $employeeFactory = $employeeFactory->state(fn (): array => [
                    'department_id' => $departmentIds->random(),
                ]);
            }

            $employeeFactory->create();
        });
    }
}
