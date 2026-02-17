<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * The number of departments to create per user.
     */
    private const DEPARTMENTS_PER_USER = 6;

    /**
     * The number of users to seed departments for.
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
            $existingDepartmentsCount = $user->departments()->count();
            $departmentsToCreate = max(0, self::DEPARTMENTS_PER_USER - $existingDepartmentsCount);

            if ($departmentsToCreate === 0) {
                return;
            }

            Department::factory()
                ->count($departmentsToCreate)
                ->forUser($user)
                ->create();
        });
    }
}
