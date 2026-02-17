<?php

namespace Tests\Feature\Departments;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_department_routes(): void
    {
        $response = $this->get(route('departments.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_create_update_or_delete_departments(): void
    {
        $department = Department::factory()->forUser(User::factory()->create())->create();

        $this->get(route('departments.create'))
            ->assertRedirect(route('login'));

        $this->post(route('departments.store'), [
            'name' => 'Operations',
            'description' => 'Team operations',
        ])->assertRedirect(route('login'));

        $this->put(route('departments.update', $department), [
            'name' => 'Updated',
        ])->assertRedirect(route('login'));

        $this->delete(route('departments.destroy', $department))
            ->assertRedirect(route('login'));
    }

    public function test_index_lists_only_owned_departments_and_supports_search(): void
    {
        $user = User::factory()->create();
        $owned = Department::factory()
            ->forUser($user)
            ->named('Engineering')
            ->create(['description' => 'Product engineering team']);

        $otherUser = User::factory()->create();
        $other = Department::factory()
            ->forUser($otherUser)
            ->named('Finance')
            ->create(['description' => 'Accounting']);

        $response = $this->actingAs($user)->get(route('departments.index'));
        $response->assertOk();
        $response->assertSee($owned->name);
        $response->assertDontSee($other->name);

        $searchResponse = $this->actingAs($user)->get(route('departments.index', ['search' => 'engineering']));
        $searchResponse->assertOk();
        $searchResponse->assertSee($owned->name);
        $searchResponse->assertDontSee($other->name);
    }

    public function test_index_search_matches_department_description(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()
            ->forUser($user)
            ->named('Operations')
            ->create(['description' => 'Handles logistics and supply chain']);

        $unrelated = Department::factory()
            ->forUser($user)
            ->named('Finance')
            ->create(['description' => 'Accounting and budgeting']);

        $response = $this->actingAs($user)->get(route('departments.index', ['search' => 'logistics']));

        $response->assertOk();
        $response->assertSee($department->name);
        $response->assertDontSee($unrelated->name);
    }

    public function test_index_search_with_no_results_shows_empty_state(): void
    {
        $user = User::factory()->create();
        Department::factory()->forUser($user)->named('Engineering')->create();

        $response = $this->actingAs($user)->get(route('departments.index', ['search' => 'nonexistent_xyz_123']));

        $response->assertOk();
        $response->assertSee('No departments match your search criteria');
    }

    public function test_index_with_blank_search_shows_all_departments(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->named('Engineering')->create();

        $response = $this->actingAs($user)->get(route('departments.index', ['search' => '    ']));

        $response->assertOk();
        $response->assertSee($department->name);
    }

    public function test_index_with_no_departments_shows_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertOk();
        $response->assertSee('Get started by adding your first department.');
    }

    public function test_index_supports_filter_dropdown_ordering_modes(): void
    {
        $user = User::factory()->create();

        $oldest = Department::factory()->forUser($user)->named('Zeta')->create([
            'description' => 'Support',
            'created_at' => now()->subDays(3),
        ]);

        $middle = Department::factory()->forUser($user)->named('Alpha')->create([
            'description' => 'Engineering',
            'created_at' => now()->subDays(2),
        ]);

        $newest = Department::factory()->forUser($user)->named('Gamma')->create([
            'description' => 'Accounting',
            'created_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->get(route('departments.index', ['filter' => 'name_alpha']))
            ->assertOk()
            ->assertViewHas('departments', function (LengthAwarePaginator $departments) use ($middle, $newest, $oldest): bool {
                return $departments->pluck('id')->take(3)->values()->all() === [
                    $middle->id,
                    $newest->id,
                    $oldest->id,
                ];
            });

        $this->actingAs($user)
            ->get(route('departments.index', ['filter' => 'name_reverse']))
            ->assertOk()
            ->assertViewHas('departments', function (LengthAwarePaginator $departments) use ($middle, $newest, $oldest): bool {
                return $departments->pluck('id')->take(3)->values()->all() === [
                    $oldest->id,
                    $newest->id,
                    $middle->id,
                ];
            });

        $this->actingAs($user)
            ->get(route('departments.index', ['filter' => 'description_alpha']))
            ->assertOk()
            ->assertViewHas('departments', function (LengthAwarePaginator $departments) use ($middle, $newest, $oldest): bool {
                return $departments->pluck('id')->take(3)->values()->all() === [
                    $newest->id,
                    $middle->id,
                    $oldest->id,
                ];
            });

        $this->actingAs($user)
            ->get(route('departments.index', ['filter' => 'description_reverse']))
            ->assertOk()
            ->assertViewHas('departments', function (LengthAwarePaginator $departments) use ($middle, $newest, $oldest): bool {
                return $departments->pluck('id')->take(3)->values()->all() === [
                    $oldest->id,
                    $middle->id,
                    $newest->id,
                ];
            });

        $this->actingAs($user)
            ->get(route('departments.index', ['filter' => 'created_newest']))
            ->assertOk()
            ->assertViewHas('departments', function (LengthAwarePaginator $departments) use ($newest): bool {
                return $departments->first()?->id === $newest->id;
            });

        $this->actingAs($user)
            ->get(route('departments.index', ['filter' => 'created_oldest']))
            ->assertOk()
            ->assertViewHas('departments', function (LengthAwarePaginator $departments) use ($oldest): bool {
                return $departments->first()?->id === $oldest->id;
            });
    }

    public function test_index_defaults_to_name_alpha_when_no_filter_specified(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertOk();
        $response->assertViewHas('filter', 'name_alpha');
    }

    public function test_index_rejects_invalid_filter_values(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('departments.index', ['filter' => 'invalid_filter']));

        $response->assertOk();
        $response->assertSessionHas('error', 'Invalid filter selected. Showing default ordering.');
        $response->assertViewHas('filter', 'name_alpha');
    }

    public function test_index_is_paginated_to_25_departments_per_page(): void
    {
        $user = User::factory()->create();

        Department::factory()
            ->forUser($user)
            ->count(30)
            ->create();

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertOk();
        $response->assertViewHas('departments', function ($departments): bool {
            return $departments->count() === 25
                && $departments->perPage() === 25
                && $departments->total() === 30;
        });
    }

    public function test_index_displays_flashed_success_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Department created successfully.'])
            ->get(route('departments.index'));

        $response->assertOk();
        $response->assertSee('Department created successfully.');
    }

    public function test_index_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to delete department right now. Please try again.'])
            ->get(route('departments.index'));

        $response->assertOk();
        $response->assertSee('Unable to delete department right now. Please try again.');
    }

    public function test_create_page_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('departments.create'));

        $response->assertOk();
    }

    public function test_create_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to create department right now. Please try again.'])
            ->get(route('departments.create'));

        $response->assertOk();
        $response->assertSee('Unable to create department right now. Please try again.');
    }

    public function test_user_can_create_department_with_normalized_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => '  Operations  ',
            'description' => '  Handles daily execution  ',
        ]);

        $response->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'user_id' => $user->id,
            'name' => 'Operations',
            'description' => 'Handles daily execution',
        ]);
    }

    public function test_user_can_create_department_with_null_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => 'Marketing',
            'description' => null,
        ]);

        $response->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'user_id' => $user->id,
            'name' => 'Marketing',
            'description' => null,
        ]);
    }

    public function test_store_validation_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_validation_rejects_short_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => 'A',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_validation_rejects_long_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => str_repeat('A', 256),
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_validation_rejects_long_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => 'Valid Name',
            'description' => str_repeat('A', 2001),
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_user_cannot_create_duplicate_department_name_for_same_user(): void
    {
        $user = User::factory()->create();

        Department::factory()->forUser($user)->named('Finance')->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => 'Finance',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_user_can_use_same_department_name_as_other_user(): void
    {
        Department::factory()->forUser(User::factory()->create())->named('Finance')->create();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'name' => 'Finance',
        ]);

        $response->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'user_id' => $user->id,
            'name' => 'Finance',
        ]);
    }

    public function test_show_page_displays_department_details_and_employee_count(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->named('Engineering')->create([
            'description' => 'Software development team',
        ]);

        Employee::factory()->forUser($user)->forDepartment($department)->count(3)->create();

        $response = $this->actingAs($user)->get(route('departments.show', $department));

        $response->assertOk();
        $response->assertSee('Engineering');
        $response->assertSee('Software development team');
        $response->assertSee('3');
    }

    public function test_show_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to load employee count right now.'])
            ->get(route('departments.show', $department));

        $response->assertOk();
        $response->assertSee('Unable to load employee count right now.');
    }

    public function test_user_can_view_edit_update_and_delete_owned_department(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->named('People')->create([
            'description' => 'Old description',
        ]);

        $this->actingAs($user)->get(route('departments.show', $department))->assertOk();
        $this->actingAs($user)->get(route('departments.edit', $department))->assertOk();

        $updateResponse = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => 'People & Culture',
            'description' => 'Updated description',
        ]);

        $updateResponse->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'People & Culture',
            'description' => 'Updated description',
        ]);

        $deleteResponse = $this->actingAs($user)->delete(route('departments.destroy', $department));
        $deleteResponse->assertRedirect(route('departments.index'));
        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_edit_page_displays_flashed_error_message(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Unable to update department right now. Please try again.'])
            ->get(route('departments.edit', $department));

        $response->assertOk();
        $response->assertSee('Unable to update department right now. Please try again.');
    }

    public function test_user_can_update_department_with_normalized_fields(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->named('Old Name')->create();

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => '  New  Name  ',
            'description' => '  Updated  description  ',
        ]);

        $response->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'New Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_user_can_update_department_and_clear_description(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->named('Engineering')->create([
            'description' => 'Old description',
        ]);

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => 'Engineering',
            'description' => null,
        ]);

        $response->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Engineering',
            'description' => null,
        ]);
    }

    public function test_update_validation_rejects_duplicate_name_for_same_user(): void
    {
        $user = User::factory()->create();
        Department::factory()->forUser($user)->named('Finance')->create();
        $department = Department::factory()->forUser($user)->named('Engineering')->create();

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => 'Finance',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_validation_allows_keeping_same_name(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->named('Engineering')->create();

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => 'Engineering',
            'description' => 'Updated description only',
        ]);

        $response->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'Engineering',
            'description' => 'Updated description only',
        ]);
    }

    public function test_update_validation_rejects_short_name(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => 'A',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_update_validation_rejects_long_description(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'name' => 'Valid Name',
            'description' => str_repeat('A', 2001),
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_user_cannot_access_other_users_departments(): void
    {
        $user = User::factory()->create();
        $otherDepartment = Department::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->get(route('departments.show', $otherDepartment))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('departments.edit', $otherDepartment))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('departments.update', $otherDepartment), [
                'name' => 'Nope',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('departments.destroy', $otherDepartment))
            ->assertForbidden();
    }

    public function test_delete_success_message_includes_department_name(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->named('Marketing')->create();

        $response = $this->actingAs($user)->delete(route('departments.destroy', $department));

        $response->assertRedirect(route('departments.index'));
        $response->assertSessionHas('success', "Department 'Marketing' deleted successfully.");
    }

    public function test_deleting_department_unassigns_department_from_employees(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->create();

        $employee = Employee::factory()
            ->forUser($user)
            ->forDepartment($department)
            ->create();

        $this->actingAs($user)
            ->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'department_id' => null,
        ]);
    }

    public function test_deleting_department_preserves_other_department_employees(): void
    {
        $user = User::factory()->create();
        $departmentToDelete = Department::factory()->forUser($user)->named('Old Dept')->create();
        $remainingDepartment = Department::factory()->forUser($user)->named('Remaining')->create();

        $unassignedEmployee = Employee::factory()
            ->forUser($user)
            ->forDepartment($departmentToDelete)
            ->create();

        $otherEmployee = Employee::factory()
            ->forUser($user)
            ->forDepartment($remainingDepartment)
            ->create();

        $this->actingAs($user)->delete(route('departments.destroy', $departmentToDelete));

        $this->assertDatabaseHas('employees', [
            'id' => $unassignedEmployee->id,
            'department_id' => null,
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $otherEmployee->id,
            'department_id' => $remainingDepartment->id,
        ]);
    }

    public function test_index_escapes_department_name_output_for_security(): void
    {
        $user = User::factory()->create();
        Department::factory()->forUser($user)->create([
            'name' => '<script>alert(1)</script>',
        ]);

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_show_escapes_department_description_for_security(): void
    {
        $user = User::factory()->create();
        $department = Department::factory()->forUser($user)->create([
            'name' => 'Safe Name',
            'description' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->actingAs($user)->get(route('departments.show', $department));

        $response->assertOk();
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
