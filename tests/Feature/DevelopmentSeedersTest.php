<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\EmployeeSeeder;
use Database\Seeders\TaskSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentSeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_employees_and_tasks_for_first_five_users(): void
    {
        User::factory()->count(5)->create();

        $this->seed(EmployeeSeeder::class);
        $this->seed(TaskSeeder::class);

        $users = User::query()->orderBy('id')->limit(5)->get();

        $this->assertCount(5, $users);
        $this->assertSame(125, Employee::query()->count());
        $this->assertSame(125, Task::query()->count());

        $users->each(function (User $user): void {
            $this->assertSame(25, $user->employees()->count());
            $this->assertSame(25, $user->tasks()->count());
        });
    }
}
