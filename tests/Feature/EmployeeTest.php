<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guests cannot access employee pages.
     */
    #[Test]
    public function guests_cannot_access_employees_index(): void
    {
        $response = $this->get(route('employees.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test guests cannot access create employee page.
     */
    #[Test]
    public function guests_cannot_access_create_employee_page(): void
    {
        $response = $this->get(route('employees.create'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated users can view the employees index.
     */
    #[Test]
    public function authenticated_users_can_view_employees_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertOk();
        $response->assertViewHas('employees');
    }

    /**
     * Test users can only see their own employees.
     */
    #[Test]
    public function users_can_only_see_their_own_employees(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Employee::factory()
            ->forUser($user)
            ->withName('John', 'Doe')
            ->create();

        Employee::factory()
            ->forUser($otherUser)
            ->withName('Jane', 'Smith')
            ->create();

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees): bool {
            $names = $employees->pluck('full_name')->all();

            return $names === ['John Doe'];
        });
    }

    /**
     * Test employees are ordered by name.
     */
    #[Test]
    public function employees_are_ordered_by_name(): void
    {
        $user = User::factory()->create();

        Employee::factory()->forUser($user)->withName('Zara', 'Adams')->create();
        Employee::factory()->forUser($user)->withName('Alice', 'Brown')->create();
        Employee::factory()->forUser($user)->withName('Alice', 'Adams')->create();

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees): bool {
            return $employees->pluck('full_name')->all() === ['Alice Adams', 'Alice Brown', 'Zara Adams'];
        });
    }

    /**
     * Test users can create employees.
     */
    #[Test]
    public function users_can_create_employees(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '555-1234',
            'work_in' => '09:00',
            'work_out' => '17:00',
            'job_title' => 'Software Engineer',
            'department' => 'Engineering',
        ]);

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success', 'Employee created successfully.');

        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '555-1234',
            'work_in' => '09:00',
            'work_out' => '17:00',
            'job_title' => 'Software Engineer',
            'department' => 'Engineering',
        ]);
    }

    /**
     * Test employee input is normalized on create.
     */
    #[Test]
    public function employee_input_is_normalized_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => '  John  ',
            'last_name' => '  Doe  ',
            'email' => ' JOHN.DOE@EXAMPLE.COM ',
            'phone_number' => '  555-1234  ',
            'job_title' => '  Software Engineer  ',
            'department' => '  Engineering  ',
        ]);

        $response->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone_number' => '555-1234',
            'job_title' => 'Software Engineer',
            'department' => 'Engineering',
        ]);
    }

    /**
     * Test employee creation requires valid data.
     */
    #[Test]
    public function employee_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => '',
            'last_name' => '',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'email']);
    }

    /**
     * Test employee creation requires minimum name length.
     */
    #[Test]
    public function employee_creation_requires_minimum_name_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => 'A',
            'last_name' => 'B',
            'email' => 'valid@example.com',
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name']);
    }

    /**
     * Test employee email must be unique.
     */
    #[Test]
    public function employee_email_must_be_unique(): void
    {
        $user = User::factory()->create();

        Employee::factory()
            ->forUser($user)
            ->withEmail('existing@example.com')
            ->create();

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test users can view their own employees.
     */
    #[Test]
    public function users_can_view_their_own_employees(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->get(route('employees.show', $employee));

        $response->assertOk();
        $response->assertViewHas('employee', fn (Employee $viewEmployee): bool => $viewEmployee->is($employee));
    }

    /**
     * Test users cannot view other users employees.
     */
    #[Test]
    public function users_cannot_view_other_users_employees(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $employee = Employee::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($user)->get(route('employees.show', $employee));

        $response->assertForbidden();
    }

    /**
     * Test users can access edit page for their own employees.
     */
    #[Test]
    public function users_can_access_edit_page_for_own_employees(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->get(route('employees.edit', $employee));

        $response->assertOk();
        $response->assertViewHas('employee', fn (Employee $viewEmployee): bool => $viewEmployee->is($employee));
    }

    /**
     * Test users cannot access edit page for other users employees.
     */
    #[Test]
    public function users_cannot_access_edit_page_for_other_users_employees(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $employee = Employee::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($user)->get(route('employees.edit', $employee));

        $response->assertForbidden();
    }

    /**
     * Test users can update their own employees.
     */
    #[Test]
    public function users_can_update_their_own_employees(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->put(route('employees.update', $employee), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone_number' => '555-9999',
            'work_in' => '08:00',
            'work_out' => '16:00',
            'job_title' => 'Senior Engineer',
            'department' => 'Technology',
        ]);

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success', 'Employee updated successfully.');

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'phone_number' => '555-9999',
            'work_in' => '08:00',
            'work_out' => '16:00',
            'job_title' => 'Senior Engineer',
            'department' => 'Technology',
        ]);
    }

    /**
     * Test employee input is normalized on update.
     */
    #[Test]
    public function employee_input_is_normalized_on_update(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->put(route('employees.update', $employee), [
            'first_name' => '  Jane  ',
            'last_name' => '  Doe  ',
            'email' => ' JANE.DOE@EXAMPLE.COM ',
            'phone_number' => '  555-4321  ',
            'job_title' => '  Manager  ',
            'department' => '  Operations  ',
        ]);

        $response->assertRedirect(route('employees.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@example.com',
            'phone_number' => '555-4321',
            'job_title' => 'Manager',
            'department' => 'Operations',
        ]);
    }

    /**
     * Test users can update employee with same email.
     */
    #[Test]
    public function users_can_update_employee_keeping_same_email(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()
            ->forUser($user)
            ->withEmail('same@example.com')
            ->create();

        $response = $this->actingAs($user)->put(route('employees.update', $employee), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'same@example.com',
        ]);

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHasNoErrors();
    }

    /**
     * Test users cannot update other users employees.
     */
    #[Test]
    public function users_cannot_update_other_users_employees(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $employee = Employee::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($user)->put(route('employees.update', $employee), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test users can delete their own employees.
     */
    #[Test]
    public function users_can_delete_their_own_employees(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->forUser($user)->create();
        $employeeId = $employee->id;

        $response = $this->actingAs($user)->delete(route('employees.destroy', $employee));

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success', 'Employee deleted successfully.');

        $this->assertDatabaseMissing('employees', [
            'id' => $employeeId,
        ]);
    }

    /**
     * Test users cannot delete other users employees.
     */
    #[Test]
    public function users_cannot_delete_other_users_employees(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $employee = Employee::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('employees.destroy', $employee));

        $response->assertForbidden();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
        ]);
    }

    /**
     * Test users can search employees by first name.
     */
    #[Test]
    public function users_can_search_employees_by_first_name(): void
    {
        $user = User::factory()->create();

        Employee::factory()
            ->forUser($user)
            ->withName('John', 'Doe')
            ->create();

        Employee::factory()
            ->forUser($user)
            ->withName('Jane', 'Smith')
            ->create();

        $response = $this->actingAs($user)->get(route('employees.index', ['search' => 'John']));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees): bool {
            return $employees->pluck('full_name')->all() === ['John Doe'];
        });
    }

    /**
     * Test users can search employees by last name.
     */
    #[Test]
    public function users_can_search_employees_by_last_name(): void
    {
        $user = User::factory()->create();

        Employee::factory()
            ->forUser($user)
            ->withName('John', 'Doe')
            ->create();

        Employee::factory()
            ->forUser($user)
            ->withName('Jane', 'Smith')
            ->create();

        $response = $this->actingAs($user)->get(route('employees.index', ['search' => 'Doe']));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees): bool {
            return $employees->pluck('full_name')->all() === ['John Doe'];
        });
    }

    /**
     * Test users can search employees by email.
     */
    #[Test]
    public function users_can_search_employees_by_email(): void
    {
        $user = User::factory()->create();

        Employee::factory()
            ->forUser($user)
            ->withEmail('john@example.com')
            ->create();

        Employee::factory()
            ->forUser($user)
            ->withEmail('jane@example.com')
            ->create();

        $response = $this->actingAs($user)->get(route('employees.index', ['search' => 'john@']));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees): bool {
            return $employees->pluck('email')->all() === ['john@example.com'];
        });
    }

    /**
     * Test empty search shows all employees.
     */
    #[Test]
    public function empty_search_shows_all_employees(): void
    {
        $user = User::factory()->create();

        Employee::factory()->forUser($user)->withName('John', 'Doe')->create();
        Employee::factory()->forUser($user)->withName('Jane', 'Smith')->create();

        $response = $this->actingAs($user)->get(route('employees.index', ['search' => '']));

        $response->assertOk();
        $response->assertViewHas('employees', function ($employees): bool {
            return $employees->pluck('full_name')->all() === ['Jane Smith', 'John Doe'];
        });
    }

    /**
     * Test all employees are displayed without pagination.
     */
    #[Test]
    public function all_employees_are_displayed(): void
    {
        $user = User::factory()->create();

        Employee::factory()->forUser($user)->count(25)->create();

        $response = $this->actingAs($user)->get(route('employees.index'));

        $response->assertOk();

        $this->assertEquals(25, $user->employees()->count());
    }

    /**
     * Test employee full name accessor.
     */
    #[Test]
    public function employee_has_full_name_accessor(): void
    {
        $employee = Employee::factory()->withName('John', 'Doe')->create();

        $this->assertEquals('John Doe', $employee->full_name);
    }
}
