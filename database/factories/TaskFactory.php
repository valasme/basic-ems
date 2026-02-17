<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Task>
     */
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dueDate = fake()->optional(0.6)->date('Y-m-d');
        $status = fake()->randomElement(Task::STATUSES);
        $priorities = array_values(array_diff(Task::PRIORITIES, ['none']));
        $priority = $status === 'completed'
            ? 'none'
            : fake()->randomElement($priorities);

        return [
            'employee_id' => Employee::factory(),
            'user_id' => function (array $attributes): int|User {
                if (! empty($attributes['employee_id'])) {
                    return Employee::query()
                        ->select(['user_id'])
                        ->findOrFail($attributes['employee_id'])
                        ->user_id;
                }

                return User::factory();
            },
            'title' => fake()->sentence(3),
            'status' => $status,
            'priority' => $priority,
            'description' => fake()->optional()->sentence(12),
            'due_date' => $dueDate,
        ];
    }

    /**
     * State for associating with a specific employee.
     */
    public function forEmployee(Employee $employee): static
    {
        return $this->state(fn (array $attributes): array => [
            'employee_id' => $employee->id,
            'user_id' => $employee->user_id,
        ]);
    }

    /**
     * State for an unassigned task that belongs to a user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'employee_id' => null,
            'user_id' => $user->id,
        ]);
    }
}
