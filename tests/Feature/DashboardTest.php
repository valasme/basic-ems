<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
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

    public function test_unverified_users_can_visit_the_dashboard_without_must_verify_email(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

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

    public function test_dashboard_shows_urgent_tasks_first_and_limits_to_seven(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();
        $urgentTasks = collect(range(1, 8))->map(function (int $index) use ($employee): Task {
            return Task::factory()->forEmployee($employee)->create([
                'title' => "Urgent task {$index}",
                'priority' => 'urgent',
                'due_date' => Carbon::now()->addDays($index),
            ]);
        });
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
        $response->assertSee($urgentTasks->first()->title);
        $response->assertDontSee($nonUrgentTask->title);
        $response->assertDontSee($otherUrgentTask->title);
        $response->assertDontSee($urgentTasks->last()->title);
        $response->assertViewHas('isShowingFallbackTasks', false);
        $response->assertViewHas('priorityTasks', function ($priorityTasks) use ($otherUrgentTask): bool {
            return $priorityTasks->count() === 7
                && ! $priorityTasks->contains('id', $otherUrgentTask->id)
                && $priorityTasks->every(fn (Task $task): bool => $task->priority === 'urgent');
        });
    }

    public function test_dashboard_shows_high_and_other_tasks_when_no_urgent_tasks_exist(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $priorityTasks = collect([
            ['title' => 'High priority payroll', 'priority' => 'high', 'due_date' => Carbon::now()->addDay()],
            ['title' => 'High priority onboarding', 'priority' => 'high', 'due_date' => Carbon::now()->addDays(2)],
            ['title' => 'Medium priority review', 'priority' => 'medium', 'due_date' => Carbon::now()->addDays(3)],
            ['title' => 'Low priority filing', 'priority' => 'low', 'due_date' => Carbon::now()->addDays(4)],
            ['title' => 'No priority cleanup', 'priority' => 'none', 'due_date' => Carbon::now()->addDays(5)],
            ['title' => 'Medium priority audit', 'priority' => 'medium', 'due_date' => Carbon::now()->addDays(6)],
            ['title' => 'Low priority reminder', 'priority' => 'low', 'due_date' => Carbon::now()->addDays(7)],
            ['title' => 'No priority archive', 'priority' => 'none', 'due_date' => Carbon::now()->addDays(8)],
        ])->map(function (array $task) use ($employee): Task {
            return Task::factory()->forEmployee($employee)->create($task);
        });

        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser($otherUser)->create();
        $otherUrgentTask = Task::factory()->forEmployee($otherEmployee)->create([
            'title' => 'Other urgent task',
            'priority' => 'urgent',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee($priorityTasks->first()->title);
        $response->assertDontSee($otherUrgentTask->title);
        $response->assertDontSee($priorityTasks->last()->title);
        $response->assertViewHas('priorityTasks', function ($priorityTasksView) use ($otherUrgentTask): bool {
            return $priorityTasksView->count() === 7
                && ! $priorityTasksView->contains('id', $otherUrgentTask->id)
                && $priorityTasksView->every(fn (Task $task): bool => $task->priority !== 'urgent');
        });
        $response->assertViewHas('isShowingFallbackTasks', true);
    }

    public function test_dashboard_handles_data_loading_errors_gracefully(): void
    {
        $user = User::factory()->create();

        Schema::drop('tasks');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('dashboardLoadError', true);
        $response->assertViewHas('employeesCount', 0);
        $response->assertViewHas('tasksCount', 0);
        $response->assertViewHas('notesCount', 0);
        $response->assertViewHas('duePaymentsPreview', fn ($employees): bool => $employees->isEmpty());
        $response->assertSee(__('Dashboard metrics are temporarily unavailable.'));
    }

    public function test_dashboard_shows_due_payments_preview_ordered_by_nearest_pay_date_and_limited_to_seven(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 10));

        $user = User::factory()->create();

        $previewEmployees = collect([
            ['first_name' => 'Pay', 'last_name' => 'Today', 'pay_day' => 10],
            ['first_name' => 'Pay', 'last_name' => 'Tomorrow', 'pay_day' => 11],
            ['first_name' => 'Pay', 'last_name' => 'SoonA', 'pay_day' => 12],
            ['first_name' => 'Pay', 'last_name' => 'SoonB', 'pay_day' => 13],
            ['first_name' => 'Pay', 'last_name' => 'SoonC', 'pay_day' => 14],
            ['first_name' => 'Pay', 'last_name' => 'SoonD', 'pay_day' => 15],
            ['first_name' => 'Pay', 'last_name' => 'SoonE', 'pay_day' => 16],
            ['first_name' => 'Pay', 'last_name' => 'TooLate', 'pay_day' => 17],
        ])->map(function (array $attributes) use ($user): Employee {
            return Employee::factory()->forUser($user)->create([
                ...$attributes,
                'email' => fake()->unique()->safeEmail(),
                'pay_amount' => 1000.00,
            ]);
        });

        $otherUser = User::factory()->create();
        $otherUserEmployee = Employee::factory()->forUser($otherUser)->create([
            'first_name' => 'Other',
            'last_name' => 'User',
            'pay_day' => 10,
            'pay_amount' => 1000.00,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Pay Today');
        $response->assertDontSee('Pay TooLate');
        $response->assertDontSee($otherUserEmployee->full_name);
        $response->assertViewHas('duePaymentsPreview', function ($duePaymentsPreview): bool {
            if ($duePaymentsPreview->count() !== 7) {
                return false;
            }

            $days = $duePaymentsPreview
                ->map(fn (Employee $employee): ?int => $employee->days_until_pay)
                ->all();

            if (in_array(null, $days, true)) {
                return false;
            }

            $sortedDays = $days;
            sort($sortedDays);

            return $days === $sortedDays;
        });

        Carbon::setTestNow();
    }
}
