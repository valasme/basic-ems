<?php

namespace Database\Factories;

use App\Models\DuePayment;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<DuePayment>
 */
class DuePaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<DuePayment>
     */
    protected $model = DuePayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $payDate = fake()->dateTimeBetween('-5 days', '+14 days');
        $status = fake()->randomElement(DuePayment::STATUSES);

        return [
            'user_id' => User::factory(),
            'employee_id' => Employee::factory(),
            'amount' => fake()->randomFloat(2, 500, 5000),
            'status' => $status,
            'notes' => fake()->optional(0.4)->sentence(),
            'pay_date' => Carbon::instance($payDate)->format('Y-m-d'),
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
            'amount' => $employee->pay_amount ?? fake()->randomFloat(2, 500, 5000),
        ]);
    }

    /**
     * State for a pending payment.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'pending',
        ]);
    }

    /**
     * State for a paid payment.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'paid',
        ]);
    }

    /**
     * State for an urgent payment (due within 1 day).
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pay_date' => Carbon::today()->addDay()->format('Y-m-d'),
            'status' => 'pending',
        ]);
    }

    /**
     * State for an overdue payment.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pay_date' => Carbon::today()->subDays(fake()->numberBetween(1, 5))->format('Y-m-d'),
            'status' => 'pending',
        ]);
    }
}
