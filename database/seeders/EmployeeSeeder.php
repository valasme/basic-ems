<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * The number of employees to create per user.
     */
    private const EMPLOYEES_PER_USER = 25;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            $users = collect([$this->getOrCreateTestUser()]);
        }

        $users->each(function (User $user): void {
            $existingEmployeesCount = $user->employees()->count();
            $employeesToCreate = max(0, self::EMPLOYEES_PER_USER - $existingEmployeesCount);

            if ($employeesToCreate === 0) {
                return;
            }

            Employee::factory()
                ->count($employeesToCreate)
                ->forUser($user)
                ->create();
        });
    }

    /**
     * Get the first existing user or create a test user.
     */
    private function getOrCreateTestUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );
    }
}
