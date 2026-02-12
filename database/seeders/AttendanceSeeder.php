<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * The number of attendance entries to create per user.
     */
    private const ATTENDANCE_PER_USER = 25;

    /**
     * The number of users to seed attendance for.
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
            ->withCount('employees')
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

            $existingCount = Attendance::query()
                ->where('user_id', $user->id)
                ->count();

            $attendanceToCreate = max(0, self::ATTENDANCE_PER_USER - $existingCount);

            if ($attendanceToCreate === 0) {
                return;
            }

            Attendance::factory()
                ->count($attendanceToCreate)
                ->state(fn (array $attributes): array => [
                    'employee_id' => $employees->random()->id,
                    'user_id' => $user->id,
                ])
                ->create();
        });
    }
}
