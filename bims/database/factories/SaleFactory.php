<?php

namespace Database\Factories;

use App\Models\HR\Employee;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'employee_id'           => Employee::factory(),
            'sale_type_id'          => SaleType::factory(),
            'customer_name'         => $this->faker->name(),
            'customer_phone'        => $this->faker->phoneNumber(),
            'sale_date'             => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'total_points'          => $this->faker->randomFloat(2, 50, 500),
            'agent_points'          => $this->faker->randomFloat(2, 25, 250),
            'status'                => 'Confirmed',
            'compensation_received' => true,
            'payroll_line_item_id'  => null,
        ];
    }

    public function unpaid(): static
    {
        return $this->state([
            'compensation_received' => true,
            'payroll_line_item_id'  => null,
        ]);
    }

    public function forPeriod(string $startDate, string $endDate): static
    {
        return $this->state([
            'sale_date' => $this->faker->dateTimeBetween($startDate, $endDate)->format('Y-m-d'),
        ]);
    }
}
