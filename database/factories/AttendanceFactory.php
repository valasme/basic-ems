<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Attendance>
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $workIn = fake()->time('H:i', '11:00');
        $workOut = Carbon::createFromFormat('H:i', $workIn)
            ->addHours(fake()->numberBetween(6, 10))
            ->format('H:i');

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
            'attendance_date' => fake()->dateTimeBetween('-14 days', '+1 day')->format('Y-m-d'),
            'work_in' => $workIn,
            'work_out' => fake()->boolean(90) ? $workOut : null,
            'note' => fake()->optional()->sentence(8),
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
