<?php

namespace Tests\Feature\DuePayments;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DuePaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_due_payments_route(): void
    {
        $response = $this->get(route('due-payments.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_lists_only_owned_employees_with_pay_day(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()
            ->forUser($user)
            ->withName('Maria', 'Lopez')
            ->create(['pay_day' => 15, 'pay_amount' => 1000.00]);

        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()
            ->forUser($otherUser)
            ->create(['pay_day' => 20, 'pay_amount' => 2000.00]);

        $response = $this->actingAs($user)->get(route('due-payments.index'));
        $response->assertOk();
        $response->assertSee($employee->full_name);
        $response->assertDontSee($otherEmployee->full_name);
    }

    public function test_index_filters_out_employees_without_pay_day(): void
    {
        $user = User::factory()->create();
        $employeeWithPayDay = Employee::factory()
            ->forUser($user)
            ->withName('John', 'Doe')
            ->create(['pay_day' => 10, 'pay_amount' => 1500.00]);

        $employeeWithoutPayDay = Employee::factory()
            ->forUser($user)
            ->withName('Jane', 'Smith')
            ->create(['pay_day' => null, 'pay_amount' => 1000.00]);

        $response = $this->actingAs($user)->get(route('due-payments.index'));
        $response->assertOk();
        $response->assertSee($employeeWithPayDay->full_name);
        $response->assertDontSee($employeeWithoutPayDay->full_name);
    }

    public function test_index_supports_search_by_employee_name(): void
    {
        $user = User::factory()->create();
        $maria = Employee::factory()
            ->forUser($user)
            ->withName('Maria', 'Lopez')
            ->create(['pay_day' => 15, 'pay_amount' => 1000.00]);

        $john = Employee::factory()
            ->forUser($user)
            ->withName('John', 'Doe')
            ->create(['pay_day' => 20, 'pay_amount' => 2000.00]);

        $searchByName = $this->actingAs($user)->get(route('due-payments.index', ['search' => 'Maria']));
        $searchByName->assertOk();
        $searchByName->assertSee($maria->full_name);
        $searchByName->assertDontSee($john->full_name);

        $searchByFullName = $this->actingAs($user)->get(route('due-payments.index', ['search' => 'Maria Lopez']));
        $searchByFullName->assertOk();
        $searchByFullName->assertSee($maria->full_name);
    }

    public function test_index_orders_employees_by_soonest_pay_date_first(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 10));

        $user = User::factory()->create();

        // Pay day is 11th (tomorrow = 1 day away)
        $tomorrow = Employee::factory()
            ->forUser($user)
            ->withName('Tomorrow', 'Pay')
            ->create(['pay_day' => 11, 'pay_amount' => 1000.00]);

        // Pay day is 25th (15 days away)
        $nextWeek = Employee::factory()
            ->forUser($user)
            ->withName('Later', 'Pay')
            ->create(['pay_day' => 25, 'pay_amount' => 2000.00]);

        // Pay day is 10th (today = 0 days away)
        $today = Employee::factory()
            ->forUser($user)
            ->withName('Today', 'Pay')
            ->create(['pay_day' => 10, 'pay_amount' => 1500.00]);

        $response = $this->actingAs($user)->get(route('due-payments.index'));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees) use ($today, $tomorrow, $nextWeek): bool {
            $ids = $employees->pluck('id')->toArray();

            return $ids[0] === $today->id
                && $ids[1] === $tomorrow->id
                && $ids[2] === $nextWeek->id;
        });

        Carbon::setTestNow();
    }

    public function test_urgency_levels_are_calculated_correctly(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 10));

        $user = User::factory()->create();

        // Urgent: pay day is today or tomorrow (0-1 days)
        $urgent = Employee::factory()->forUser($user)->create([
            'pay_day' => 10,
            'pay_amount' => 1000.00,
        ]);

        // Soon: pay day is in 2-3 days
        $soon = Employee::factory()->forUser($user)->create([
            'pay_day' => 12,
            'pay_amount' => 1000.00,
        ]);

        // Upcoming: pay day is in 4-7 days
        $upcoming = Employee::factory()->forUser($user)->create([
            'pay_day' => 15,
            'pay_amount' => 1000.00,
        ]);

        // Scheduled: pay day is 8+ days away
        $scheduled = Employee::factory()->forUser($user)->create([
            'pay_day' => 25,
            'pay_amount' => 1000.00,
        ]);

        $this->assertEquals('urgent', $urgent->pay_urgency);
        $this->assertEquals('soon', $soon->pay_urgency);
        $this->assertEquals('upcoming', $upcoming->pay_urgency);
        $this->assertEquals('scheduled', $scheduled->pay_urgency);

        Carbon::setTestNow();
    }

    public function test_days_until_pay_is_calculated_correctly(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 10));

        $user = User::factory()->create();

        $todayEmployee = Employee::factory()->forUser($user)->create([
            'pay_day' => 10,
            'pay_amount' => 1000.00,
        ]);

        $tomorrowEmployee = Employee::factory()->forUser($user)->create([
            'pay_day' => 11,
            'pay_amount' => 1000.00,
        ]);

        $nextMonthEmployee = Employee::factory()->forUser($user)->create([
            'pay_day' => 5,
            'pay_amount' => 1000.00,
        ]);

        $this->assertEquals(0, $todayEmployee->days_until_pay);
        $this->assertEquals(1, $tomorrowEmployee->days_until_pay);
        // Feb 10 to Mar 5 is 23 days
        $this->assertEquals(23, $nextMonthEmployee->days_until_pay);

        Carbon::setTestNow();
    }
}
