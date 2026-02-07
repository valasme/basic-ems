<?php

namespace Tests\Feature\Tasks;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_task_routes(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_lists_only_owned_tasks_and_supports_search(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->withName('Maria', 'Lopez')->create();
        $task = Task::factory()->forEmployee($employee)->create([
            'title' => 'Prepare reports',
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

        $searchByTitle = $this->actingAs($user)->get(route('tasks.index', ['search' => 'reports']));
        $searchByTitle->assertOk();
        $searchByTitle->assertSee($task->title);

        $searchByEmployee = $this->actingAs($user)->get(route('tasks.index', ['search' => 'Maria']));
        $searchByEmployee->assertOk();
        $searchByEmployee->assertSee($task->title);
    }

    public function test_index_is_paginated_to_25_tasks_per_page(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        Task::factory()
            ->forEmployee($employee)
            ->count(30)
            ->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertViewHas('tasks', function ($tasks): bool {
            return $tasks->count() === 25
                && $tasks->perPage() === 25
                && $tasks->total() === 30;
        });
    }

    public function test_user_can_create_task_for_their_employee(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'employee_id' => $employee->id,
            'title' => '  Prepare onboarding  ',
            'status' => Task::STATUSES[0],
            'description' => '  Collect documents and schedule a walkthrough. ',
            'due_date' => '2026-02-10',
        ]);

        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'employee_id' => $employee->id,
            'title' => 'Prepare onboarding',
            'status' => Task::STATUSES[0],
            'description' => 'Collect documents and schedule a walkthrough.',
            'due_date' => Carbon::parse('2026-02-10')->startOfDay()->toDateTimeString(),
        ]);
    }

    public function test_store_validation_rejects_invalid_status_due_date_and_employee(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'employee_id' => $otherEmployee->id,
            'title' => 'Ok title',
            'status' => 'invalid',
            'due_date' => 'not-a-date',
        ]);

        $response->assertSessionHasErrors(['employee_id', 'status', 'due_date']);
    }

    public function test_user_can_view_and_edit_their_task(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();
        $task = Task::factory()->forEmployee($employee)->create();

        $showResponse = $this->actingAs($user)->get(route('tasks.show', $task));
        $showResponse->assertOk();
        $showResponse->assertSee($task->title);

        $editResponse = $this->actingAs($user)->get(route('tasks.edit', $task));
        $editResponse->assertOk();
    }

    public function test_user_cannot_view_or_edit_other_users_task(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();
        $otherTask = Task::factory()->forEmployee($otherEmployee)->create();

        $this->actingAs($user)
            ->get(route('tasks.show', $otherTask))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('tasks.edit', $otherTask))
            ->assertForbidden();
    }

    public function test_user_can_update_their_task(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();
        $task = Task::factory()->forEmployee($employee)->create([
            'title' => 'Draft outline',
            'status' => Task::STATUSES[0],
        ]);

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'employee_id' => $employee->id,
            'title' => 'Finalize outline',
            'status' => Task::STATUSES[1],
            'description' => 'Finalize sections A and B.',
            'due_date' => '2026-02-15',
        ]);

        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Finalize outline',
            'status' => Task::STATUSES[1],
            'description' => 'Finalize sections A and B.',
            'due_date' => Carbon::parse('2026-02-15')->startOfDay()->toDateTimeString(),
        ]);
    }

    public function test_user_cannot_update_other_users_task(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();
        $otherTask = Task::factory()->forEmployee($otherEmployee)->create();

        $this->actingAs($user)
            ->put(route('tasks.update', $otherTask), [
                'employee_id' => $otherEmployee->id,
                'title' => 'Nope',
                'status' => Task::STATUSES[0],
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_their_task(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();
        $task = Task::factory()->forEmployee($employee)->create();

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));
        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_other_users_task(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();
        $otherTask = Task::factory()->forEmployee($otherEmployee)->create();

        $this->actingAs($user)
            ->delete(route('tasks.destroy', $otherTask))
            ->assertForbidden();
    }
}
