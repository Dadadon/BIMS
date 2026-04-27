<?php

namespace Database\Factories;

use App\Models\HR\Company;
use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        static $seq = 1;
        return [
            'company_id'         => Company::factory(),
            'employee_code'      => 'EMP-' . str_pad($seq++, 5, '0', STR_PAD_LEFT),
            'firstname'          => $this->faker->firstName(),
            'lastname'           => $this->faker->lastName(),
            'middle_name'        => null,
            'email'              => $this->faker->unique()->safeEmail(),
            'employment_type'    => 'Regular',
            'employment_status'  => 'Active',
            'is_salaried'        => false,
            'base_rate'          => $this->faker->randomFloat(2, 50, 200),
            'start_date'         => $this->faker->dateTimeBetween('-3 years', '-6 months')->format('Y-m-d'),
        ];
    }

    public function salaried(float $rate = 15000): static
    {
        return $this->state(['is_salaried' => true, 'base_rate' => $rate]);
    }

    public function hourly(float $rate = 75): static
    {
        return $this->state(['is_salaried' => false, 'base_rate' => $rate]);
    }

    public function withSip(string $extension = null): static
    {
        return $this->state([
            'sip_extension' => $extension ?? $this->faker->numerify('1###'),
            'sip_password'  => $this->faker->password(8),
        ]);
    }
}
