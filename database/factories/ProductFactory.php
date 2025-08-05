<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true), // e.g., "Cool Shirt"
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(1000, 5000), // 10.00 to 500.00
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
}
