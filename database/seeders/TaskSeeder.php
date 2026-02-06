<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * The number of tasks to create per user.
     */
    private const TASKS_PER_USER = 25;

    /**
     * The number of users to seed tasks for.
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
            ->withCount('tasks')
            ->with(['employees:id,user_id'])
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

            $tasksToCreate = max(0, self::TASKS_PER_USER - (int) $user->tasks_count);

            if ($tasksToCreate === 0) {
                return;
            }

            Task::factory()
                ->count($tasksToCreate)
                ->state(fn (): array => [
                    'employee_id' => $employees->random()->id,
                    'user_id' => $user->id,
                ])
                ->create();
        });
    }
}
