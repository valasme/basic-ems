<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_successful_response(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_tasks_index_only_shows_tasks_for_owned_employees(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();
        $task = Task::factory()->forEmployee($employee)->create([
            'title' => 'Quarterly reports',
        ]);

        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser($otherUser)->create();
        $otherTask = Task::factory()->forEmployee($otherEmployee)->create([
            'title' => 'Private task',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSee($task->title);
        $response->assertDontSee($otherTask->title);
    }

    public function test_user_can_create_task_for_their_employee(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'employee_id' => $employee->id,
            'title' => 'Prepare onboarding',
            'status' => 'pending',
            'description' => 'Collect documents and schedule a walkthrough.',
            'due_date' => '2026-02-10',
        ]);

        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'employee_id' => $employee->id,
            'title' => 'Prepare onboarding',
            'status' => 'pending',
            'description' => 'Collect documents and schedule a walkthrough.',
            'due_date' => Carbon::parse('2026-02-10')->startOfDay()->toDateTimeString(),
        ]);
    }
}
