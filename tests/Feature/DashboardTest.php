<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_shows_quick_stats_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Employee::factory()->forUser($user)->count(3)->create();
        $employee = Employee::factory()->forUser($user)->create();
        Task::factory()->forEmployee($employee)->count(4)->create();
        Note::factory()->forUser($user)->count(5)->create();

        Employee::factory()->forUser($otherUser)->count(7)->create();
        $otherEmployee = Employee::factory()->forUser($otherUser)->create();
        Task::factory()->forEmployee($otherEmployee)->count(8)->create();
        Note::factory()->forUser($otherUser)->count(9)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('employeesCount', 4);
        $response->assertViewHas('tasksCount', 4);
        $response->assertViewHas('notesCount', 5);
    }

    public function test_dashboard_only_shows_urgent_tasks_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();
        $urgentTask = Task::factory()->forEmployee($employee)->create([
            'title' => 'Urgent payroll review',
            'priority' => 'urgent',
        ]);
        $unassignedUrgentTask = Task::factory()->forUser($user)->create([
            'title' => 'Unassigned urgent task',
            'priority' => 'urgent',
        ]);
        $nonUrgentTask = Task::factory()->forEmployee($employee)->create([
            'title' => 'Medium priority filing',
            'priority' => 'medium',
        ]);

        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser($otherUser)->create();
        $otherUrgentTask = Task::factory()->forEmployee($otherEmployee)->create([
            'title' => 'Other urgent task',
            'priority' => 'urgent',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee($urgentTask->title);
        $response->assertSee($unassignedUrgentTask->title);
        $response->assertSee(__('Unassigned'));
        $response->assertDontSee($nonUrgentTask->title);
        $response->assertDontSee($otherUrgentTask->title);
        $response->assertViewHas('urgentTasks', function ($urgentTasks) use ($urgentTask, $unassignedUrgentTask, $otherUrgentTask): bool {
            return $urgentTasks->contains('id', $urgentTask->id)
                && $urgentTasks->contains('id', $unassignedUrgentTask->id)
                && ! $urgentTasks->contains('id', $otherUrgentTask->id)
                && $urgentTasks->every(fn (Task $task): bool => $task->priority === 'urgent');
        });
    }
}
