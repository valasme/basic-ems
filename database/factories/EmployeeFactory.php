<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Employee>
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'work_in' => fake()->time('H:i'),
            'work_out' => fake()->time('H:i'),
            'pay_day' => fake()->optional()->numberBetween(1, 28),
            'pay_amount' => fake()->randomFloat(2, 1200, 3000),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Engineering', 'Marketing', 'Sales', 'Human Resources', 'Finance', 'Operations']),
        ];
    }

    /**
     * State for associating with a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * State for creating an employee with a specific email.
     */
    public function withEmail(string $email): static
    {
        return $this->state(fn (array $attributes): array => [
            'email' => Str::of($email)->trim()->lower()->toString(),
        ]);
    }

    /**
     * State for creating an employee with a specific name.
     */
    public function withName(string $firstName, string $lastName): static
    {
        return $this->state(fn (array $attributes): array => [
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }
}
