<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name'     => $this->faker->jobTitle(),
            'slug'     => $this->faker->unique()->slug(2),
            'is_admin' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(['slug' => 'system_admin', 'name' => 'System Admin', 'is_admin' => true]);
    }

    public function manager(): static
    {
        return $this->state(['slug' => 'manager', 'name' => 'Manager', 'is_admin' => true]);
    }

    public function employee(): static
    {
        return $this->state(['slug' => 'employee', 'name' => 'Employee', 'is_admin' => false]);
    }
}
