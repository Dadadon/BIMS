<?php

namespace Database\Factories;

use App\Models\Sales\SaleType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleTypeFactory extends Factory
{
    protected $model = SaleType::class;

    public function definition(): array
    {
        $categories = ['Internet', 'TV', 'Phone', 'Bundle', 'Mobile'];
        $portals    = ['Xfinity', 'AT&T', 'Verizon', 'Spectrum', null];
        return [
            'product_category' => $this->faker->randomElement($categories),
            'portal'           => $this->faker->randomElement($portals),
            'product_code'     => strtoupper($this->faker->lexify('???-###')),
            'total_points'     => $this->faker->numberBetween(50, 300),
            'points_per_agent' => $this->faker->randomFloat(2, 25, 150),
            'is_active'        => true,
        ];
    }
}
