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

    public function test_guests_cannot_create_update_or_delete_employees(): void
    {
        $employee = Employee::factory()->forUser(User::factory()->create())->create();

        $this->get(route('employees.create'))
            ->assertRedirect(route('login'));

        $this->post(route('employees.store'), [
            'first_name' => 'Ava',
            'last_name' => 'Stone',
            'email' => 'ava@example.com',
        ])->assertRedirect(route('login'));

        $this->put(route('employees.update', $employee), [
            'first_name' => 'Nope',
            'last_name' => 'Nope',
            'email' => 'nope@example.com',
        ])->assertRedirect(route('login'));

        $this->delete(route('employees.destroy', $employee))
            ->assertRedirect(route('login'));
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

    public function test_create_page_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('employees.create'));

        $response->assertOk();
    }

    public function test_create_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to create employee right now. Please try again.'])
            ->get(route('employees.create'));

        $response->assertOk();
        $response->assertSee('Unable to create employee right now. Please try again.');
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

    public function test_user_can_create_employee_with_minimal_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => 'Dimitris',
            'last_name' => 'Valasellis',
            'email' => 'valasellis@example.com',
        ]);

        $response->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'first_name' => 'Dimitris',
            'last_name' => 'Valasellis',
            'email' => 'valasellis@example.com',
            'pay_amount' => null,
            'pay_salary' => null,
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

    public function test_store_validation_rejects_invalid_pay_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => 'Taylor',
            'last_name' => 'Morgan',
            'email' => 'taylor@example.com',
            'work_in' => '9am',
            'work_out' => '5pm',
            'pay_day' => 0,
            'pay_amount' => -10,
        ]);

        $response->assertSessionHasErrors(['work_in', 'work_out', 'pay_day', 'pay_amount']);
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

    public function test_edit_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to update employee right now. Please try again.'])
            ->get(route('employees.edit', $employee));

        $response->assertOk();
        $response->assertSee('Unable to update employee right now. Please try again.');
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

    public function test_user_can_update_employee_with_normalized_fields(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->withEmail('before@example.com')->create();

        $response = $this->actingAs($user)->put(route('employees.update', $employee), [
            'first_name' => '  Jamie ',
            'last_name' => '  Ray ',
            'email' => ' JAMIE.RAY@EXAMPLE.com ',
            'phone_number' => ' 555 777 3333 ',
            'work_in' => '08:00',
            'work_out' => '16:00',
            'job_title' => '  Manager ',
            'department' => '  People Ops ',
        ]);

        $response->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Jamie',
            'last_name' => 'Ray',
            'email' => 'jamie.ray@example.com',
            'phone_number' => '555 777 3333',
            'job_title' => 'Manager',
            'department' => 'People Ops',
        ]);
    }

    public function test_update_validation_rejects_duplicate_email_and_invalid_inputs(): void
    {
        $user = User::factory()->create();
        $existing = Employee::factory()->forUser($user)->withEmail('taken@example.com')->create();
        $employee = Employee::factory()->forUser($user)->withEmail('current@example.com')->create();

        $response = $this->actingAs($user)->put(route('employees.update', $employee), [
            'first_name' => 'Jo',
            'last_name' => 'Da',
            'email' => $existing->email,
            'work_in' => '9am',
            'work_out' => '5pm',
            'pay_day' => 32,
            'pay_amount' => -1,
        ]);

        $response->assertSessionHasErrors(['email', 'work_in', 'work_out', 'pay_day', 'pay_amount']);
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

    public function test_index_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to delete employee right now. Please try again.'])
            ->get(route('employees.index'));

        $response->assertOk();
        $response->assertSee('Unable to delete employee right now. Please try again.');
    }

    public function test_user_cannot_delete_other_users_employee(): void
    {
        $user = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->delete(route('employees.destroy', $otherEmployee))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_from_attendance_routes(): void
    {
        $response = $this->get(route('attendances.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_attendance_schedule_is_read_only_without_create_show_or_edit_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/attendances/create')->assertNotFound();
        $this->actingAs($user)->get('/attendances/1')->assertNotFound();
        $this->actingAs($user)->get('/attendances/1/edit')->assertNotFound();
    }

    public function test_attendance_index_lists_only_owned_entries_and_sorts_by_work_in_priority(): void
    {
        $user = User::factory()->create();

        $firstEmployee = Employee::factory()
            ->forUser($user)
            ->withName('Aaron', 'Early')
            ->create(['work_in' => '08:00']);

        $lastEmployee = Employee::factory()
            ->forUser($user)
            ->withName('Liam', 'Late')
            ->create(['work_in' => '10:00']);

        $otherUser = User::factory()->create();
        $otherEmployee = Employee::factory()->forUser($otherUser)->withName('Other', 'User')->create(['work_in' => '07:00']);

        $response = $this->actingAs($user)->get(route('attendances.index'));
        $response->assertOk();
        $response->assertSeeInOrder([
            $firstEmployee->full_name,
            $lastEmployee->full_name,
        ]);
        $response->assertDontSee($otherEmployee->full_name);
    }

    public function test_attendance_schedule_supports_search_by_employee_name(): void
    {
        $user = User::factory()->create();
        $matching = Employee::factory()->forUser($user)->withName('Mina', 'Stark')->create(['work_in' => '09:00', 'work_out' => '17:00']);
        $notMatching = Employee::factory()->forUser($user)->withName('John', 'West')->create(['work_in' => '08:00', 'work_out' => '16:00']);

        $response = $this->actingAs($user)->get(route('attendances.index', ['search' => 'Mina']));

        $response->assertOk();
        $response->assertSee($matching->full_name);
        $response->assertDontSee($notMatching->full_name);
    }

    public function test_attendance_schedule_search_is_scoped_per_user_even_with_same_name(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $owned = Employee::factory()->forUser($user)->withName('Alex', 'Stone')->create(['work_in' => '08:00']);
        $other = Employee::factory()->forUser($otherUser)->withName('Alex', 'Stone')->create(['work_in' => '07:00']);

        $response = $this->actingAs($user)->get(route('attendances.index', ['search' => 'Alex Stone']));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees) use ($owned, $other): bool {
            return $employees->count() === 1
                && $employees->first()->id === $owned->id
                && $employees->first()->id !== $other->id;
        });
    }

    public function test_attendance_schedule_orders_employees_with_null_work_in_last(): void
    {
        $user = User::factory()->create();

        $early = Employee::factory()->forUser($user)->withName('Early', 'Bird')->create(['work_in' => '07:30']);
        $late = Employee::factory()->forUser($user)->withName('Late', 'Shift')->create(['work_in' => '10:30']);
        $unscheduled = Employee::factory()->forUser($user)->withName('No', 'Schedule')->create(['work_in' => null]);

        $response = $this->actingAs($user)->get(route('attendances.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            $early->full_name,
            $late->full_name,
            $unscheduled->full_name,
        ]);
    }

    public function test_attendance_schedule_treats_blank_search_as_unfiltered_results(): void
    {
        $user = User::factory()->create();

        $first = Employee::factory()->forUser($user)->withName('First', 'Worker')->create(['work_in' => '08:00']);
        $second = Employee::factory()->forUser($user)->withName('Second', 'Worker')->create(['work_in' => '09:00']);

        $response = $this->actingAs($user)->get(route('attendances.index', ['search' => '    ']));

        $response->assertOk();
        $response->assertSee($first->full_name);
        $response->assertSee($second->full_name);
    }

    public function test_attendance_index_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to load attendance right now. Please try again.'])
            ->get(route('attendances.index'));

        $response->assertOk();
        $response->assertSee('Unable to load attendance right now. Please try again.');
    }

    public function test_attendance_schedule_rejects_unsupported_http_methods_with_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/attendances', [])
            ->assertStatus(405);

        $this->actingAs($user)
            ->put('/attendances/1', [])
            ->assertNotFound();

        $this->actingAs($user)
            ->delete('/attendances/1')
            ->assertNotFound();
    }

    public function test_attendance_schedule_escapes_employee_name_output_for_security(): void
    {
        $user = User::factory()->create();
        Employee::factory()->forUser($user)->create([
            'first_name' => '<script>alert(1)</script>',
            'last_name' => 'Safe',
            'work_in' => '08:00',
            'work_out' => '16:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendances.index'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script> Safe', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt; Safe', false);
    }
}
