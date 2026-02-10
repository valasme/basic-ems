<?php

namespace Tests\Feature\Employees;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_employee_routes(): void
    {
        $response = $this->get(route('employees.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_lists_only_owned_employees_and_supports_search(): void
    {
        $user = User::factory()->create();
        $owned = Employee::factory()
            ->forUser($user)
            ->withName('Ava', 'Stone')
            ->withEmail('ava@example.com')
            ->create();

        $otherUser = User::factory()->create();
        $other = Employee::factory()
            ->forUser($otherUser)
            ->withName('Zane', 'North')
            ->withEmail('zane@example.com')
            ->create();

        $response = $this->actingAs($user)->get(route('employees.index'));
        $response->assertOk();
        $response->assertSee($owned->full_name);
        $response->assertDontSee($other->full_name);

        $searchResponse = $this->actingAs($user)->get(route('employees.index', ['search' => 'ava@example.com']));
        $searchResponse->assertOk();
        $searchResponse->assertSee($owned->full_name);
        $searchResponse->assertDontSee($other->full_name);

        $fullNameSearch = $this->actingAs($user)->get(route('employees.index', ['search' => 'Ava Stone']));
        $fullNameSearch->assertOk();
        $fullNameSearch->assertSee($owned->full_name);
        $fullNameSearch->assertDontSee($other->full_name);
    }

    public function test_index_is_paginated_to_25_employees_per_page(): void
    {
        $user = User::factory()->create();

        Employee::factory()
            ->forUser($user)
            ->count(30)
            ->create();

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees): bool {
            return $employees->count() === 25
                && $employees->perPage() === 25
                && $employees->total() === 30;
        });
    }

    public function test_user_can_create_employee_with_normalized_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => '  Ava ',
            'last_name' => '  Stone ',
            'email' => ' AVA@Example.com ',
            'phone_number' => ' 555 123 4567 ',
            'work_in' => '09:00',
            'work_out' => '17:00',
            'pay_day' => 15,
            'pay_amount' => '1750.50',
            'job_title' => '  Coordinator ',
            'department' => '  Operations ',
        ]);

        $response->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'first_name' => 'Ava',
            'last_name' => 'Stone',
            'email' => 'ava@example.com',
            'phone_number' => '555 123 4567',
            'work_in' => '09:00',
            'work_out' => '17:00',
            'pay_day' => 15,
            'pay_amount' => '1750.50',
            'pay_salary' => '21006.00',
            'job_title' => 'Coordinator',
            'department' => 'Operations',
        ]);
    }

    public function test_store_validation_requires_fields_and_unique_email(): void
    {
        $user = User::factory()->create();
        $existing = Employee::factory()->forUser($user)->withEmail('unique@example.com')->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => '',
            'last_name' => '',
            'email' => $existing->email,
            'work_in' => '9am',
            'work_out' => '5pm',
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'work_in', 'work_out']);
    }

    public function test_user_can_view_and_edit_their_employee(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $showResponse = $this->actingAs($user)->get(route('employees.show', $employee));
        $showResponse->assertOk();
        $showResponse->assertSee($employee->full_name);

        $editResponse = $this->actingAs($user)->get(route('employees.edit', $employee));
        $editResponse->assertOk();
    }

    public function test_user_cannot_view_or_edit_other_users_employee(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->get(route('employees.show', $otherEmployee))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('employees.edit', $otherEmployee))
            ->assertForbidden();
    }

    public function test_user_can_update_their_employee(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->withEmail('original@example.com')->create();

        $response = $this->actingAs($user)->put(route('employees.update', $employee), [
            'first_name' => 'Jamie',
            'last_name' => 'Ray',
            'email' => 'original@example.com',
            'phone_number' => '555-999-0000',
            'work_in' => '08:30',
            'work_out' => '16:30',
            'pay_day' => 20,
            'pay_amount' => '1900.00',
            'job_title' => 'Analyst',
            'department' => 'Finance',
        ]);

        $response->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Jamie',
            'last_name' => 'Ray',
            'email' => 'original@example.com',
            'job_title' => 'Analyst',
            'department' => 'Finance',
            'pay_day' => 20,
            'pay_amount' => '1900.00',
            'pay_salary' => '22800.00',
        ]);
    }

    public function test_user_cannot_update_other_users_employee(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->put(route('employees.update', $otherEmployee), [
                'first_name' => 'Nope',
                'last_name' => 'Nope',
                'email' => 'nope@example.com',
                'work_in' => '09:00',
                'work_out' => '17:00',
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_their_employee(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->delete(route('employees.destroy', $employee));
        $response->assertRedirect(route('employees.index'));

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    public function test_user_cannot_delete_other_users_employee(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->delete(route('employees.destroy', $otherEmployee))
            ->assertForbidden();
    }
}
