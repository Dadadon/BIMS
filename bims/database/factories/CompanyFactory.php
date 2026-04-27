<?php

namespace Database\Factories;

use App\Models\HR\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name'             => $this->faker->company(),
            'commission_model' => 'sale_type_rate',
            'commission_rate'  => 0.00,
            'is_primary'       => false,
        ];
    }

    public function primary(): static
    {
        return $this->state(['is_primary' => true]);
    }
}
