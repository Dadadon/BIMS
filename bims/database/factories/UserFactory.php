<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        // Roles are seeded by migration — fetch existing employee role
        $roleId = Role::where('slug', 'employee')->value('id')
                  ?? Role::first()?->id
                  ?? 1;

        return [
            'role_id'           => $roleId,
            'employee_id'       => null,
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'acc_type'          => 'employee',
            'status'            => true,
            'remember_token'    => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn() => [
            'role_id'  => Role::where('slug', 'system_admin')->value('id') ?? 1,
            'acc_type' => 'admin',
        ]);
    }

    public function withEmployee(): static
    {
        return $this->state(fn() => [
            'employee_id' => \App\Models\HR\Employee::factory(),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
