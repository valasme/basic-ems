<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\EmployeeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployeeSeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_seeds_employees_for_users_that_have_none(): void
    {
        $userWithExistingEmployees = User::factory()->create();
        $userWithoutEmployees = User::factory()->create();

        Employee::factory()->forUser($userWithExistingEmployees)->create();

        $this->seed(EmployeeSeeder::class);

        $this->assertSame(25, $userWithExistingEmployees->employees()->count());
        $this->assertSame(25, $userWithoutEmployees->employees()->count());

        $this->assertDatabaseCount('employees', 50);
    }
}
