<?php

namespace Tests\Feature\CriticalTasks;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CriticalTaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_critical_tasks_route(): void
    {
        $response = $this->get(route('critical-tasks.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_critical_tasks_is_read_only_route(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/critical-tasks/create')->assertNotFound();
        $this->actingAs($user)->get('/critical-tasks/1')->assertNotFound();
        $this->actingAs($user)->get('/critical-tasks/1/edit')->assertNotFound();

        $this->actingAs($user)->post('/critical-tasks', [])->assertStatus(405);
        $this->actingAs($user)->put('/critical-tasks/1', [])->assertNotFound();
        $this->actingAs($user)->delete('/critical-tasks/1')->assertNotFound();
    }

    public function test_index_lists_only_owned_non_completed_tasks(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->withName('Maria', 'Lopez')->create();

        $pendingTask = Task::factory()->forEmployee($employee)->create([
            'title' => 'Pending owned task',
            'status' => 'pending',
        ]);

        $inProgressTask = Task::factory()->forEmployee($employee)->create([
            'title' => 'In progress owned task',
            'status' => 'in_progress',
        ]);

        $completedTask = Task::factory()->forEmployee($employee)->create([
            'title' => 'Completed owned task',
            'status' => 'completed',
            'priority' => 'none',
        ]);

        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser($otherUser)->create();
        $otherTask = Task::factory()->forEmployee($otherEmployee)->create([
            'title' => 'Other user task',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('critical-tasks.index'));

        $response->assertOk();
        $response->assertSee($pendingTask->title);
        $response->assertSee($inProgressTask->title);
        $response->assertDontSee($completedTask->title);
        $response->assertDontSee($otherTask->title);
    }

    public function test_index_supports_search_by_task_title_and_employee_name(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->withName('Rico', 'Santos')->create();

        $targetTask = Task::factory()->forEmployee($employee)->create([
            'title' => 'Prepare urgent audit sheet',
            'status' => 'pending',
        ]);

        $otherTask = Task::factory()->forEmployee($employee)->create([
            'title' => 'Archive old files',
            'status' => 'pending',
        ]);

        $searchByTitle = $this->actingAs($user)->get(route('critical-tasks.index', [
            'search' => 'audit',
        ]));

        $searchByTitle->assertOk();
        $searchByTitle->assertSee($targetTask->title);
        $searchByTitle->assertDontSee($otherTask->title);

        $searchByEmployee = $this->actingAs($user)->get(route('critical-tasks.index', [
            'search' => 'Rico Santos',
        ]));

        $searchByEmployee->assertOk();
        $searchByEmployee->assertSee($targetTask->title);
    }

    public function test_time_plus_priority_filter_orders_by_due_date_then_priority(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 13));

        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $dueTodayMedium = Task::factory()->forEmployee($employee)->create([
            'title' => 'Due today medium',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => '2026-02-13',
        ]);

        $dueTomorrowUrgent = Task::factory()->forEmployee($employee)->create([
            'title' => 'Due tomorrow urgent',
            'status' => 'pending',
            'priority' => 'urgent',
            'due_date' => '2026-02-14',
        ]);

        $dueTomorrowHigh = Task::factory()->forEmployee($employee)->create([
            'title' => 'Due tomorrow high',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => '2026-02-14',
        ]);

        $noDueDateUrgent = Task::factory()->forEmployee($employee)->create([
            'title' => 'No due date urgent',
            'status' => 'pending',
            'priority' => 'urgent',
            'due_date' => null,
        ]);

        $response = $this->actingAs($user)->get(route('critical-tasks.index', [
            'filter' => 'time_priority',
        ]));

        $response->assertOk();
        $response->assertViewHas('tasks', function ($tasks) use ($dueTodayMedium, $dueTomorrowUrgent, $dueTomorrowHigh, $noDueDateUrgent): bool {
            $ids = $tasks->pluck('id')->toArray();

            return $ids === [
                $dueTodayMedium->id,
                $dueTomorrowUrgent->id,
                $dueTomorrowHigh->id,
                $noDueDateUrgent->id,
            ];
        });

        Carbon::setTestNow();
    }

    public function test_priority_only_filter_orders_by_priority_then_due_date(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 13));

        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $urgentSoon = Task::factory()->forEmployee($employee)->create([
            'title' => 'Urgent soon',
            'status' => 'pending',
            'priority' => 'urgent',
            'due_date' => '2026-02-14',
        ]);

        $urgentLater = Task::factory()->forEmployee($employee)->create([
            'title' => 'Urgent later',
            'status' => 'pending',
            'priority' => 'urgent',
            'due_date' => '2026-02-18',
        ]);

        $highOverdue = Task::factory()->forEmployee($employee)->create([
            'title' => 'High overdue',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => '2026-02-10',
        ]);

        $mediumNoDate = Task::factory()->forEmployee($employee)->create([
            'title' => 'Medium no date',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => null,
        ]);

        $response = $this->actingAs($user)->get(route('critical-tasks.index', [
            'filter' => 'priority_only',
        ]));

        $response->assertOk();
        $response->assertViewHas('tasks', function ($tasks) use ($urgentSoon, $urgentLater, $highOverdue, $mediumNoDate): bool {
            $ids = $tasks->pluck('id')->toArray();

            return $ids === [
                $urgentSoon->id,
                $urgentLater->id,
                $highOverdue->id,
                $mediumNoDate->id,
            ];
        });

        Carbon::setTestNow();
    }

    public function test_invalid_filter_defaults_to_time_priority_with_error_message(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        Task::factory()->forEmployee($employee)->create([
            'title' => 'Sample critical task',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('critical-tasks.index', [
            'filter' => 'broken-filter',
        ]));

        $response->assertOk();
        $response->assertSee('Invalid filter selected. Showing default ranking.');
        $response->assertViewHas('filter', 'time_priority');
    }

    public function test_index_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to load critical tasks right now. Please try again.'])
            ->get(route('critical-tasks.index'));

        $response->assertOk();
        $response->assertSee('Unable to load critical tasks right now. Please try again.');
    }

    public function test_filtering_ui_shows_clear_button_when_filter_is_not_default(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('critical-tasks.index', [
            'filter' => 'priority_only',
        ]));

        $response->assertOk();
        $response->assertSee('Clear');
    }
}
