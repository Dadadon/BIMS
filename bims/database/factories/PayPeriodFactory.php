<?php

namespace Database\Factories;

use App\Models\Payroll\PayPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayPeriodFactory extends Factory
{
    protected $model = PayPeriod::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 months', '-1 month');
        $end   = (clone $start)->modify('+14 days');
        return [
            'label'       => $this->faker->date('F Y') . ' – 1st Half',
            'period_type' => 'biweekly',
            'start_date'  => $start->format('Y-m-d'),
            'end_date'    => $end->format('Y-m-d'),
            'pay_date'    => $end->format('Y-m-d'),
            'status'      => 'open',
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => 'open']);
    }

    public function processing(): static
    {
        return $this->state(['status' => 'processing']);
    }

    public function closed(): static
    {
        return $this->state(['status' => 'closed']);
    }

    public function forDates(string $start, string $end): static
    {
        return $this->state([
            'start_date' => $start,
            'end_date'   => $end,
            'pay_date'   => $end,
        ]);
    }
}
