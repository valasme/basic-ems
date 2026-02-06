<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Task;
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

        return [
            'employee_id' => Employee::factory(),
            'user_id' => fn (array $attributes): int => Employee::query()
                ->select(['user_id'])
                ->findOrFail($attributes['employee_id'])
                ->user_id,
            'title' => fake()->sentence(3),
            'status' => fake()->randomElement(Task::STATUSES),
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
}
