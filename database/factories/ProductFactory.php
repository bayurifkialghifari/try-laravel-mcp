<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports', 'Toys', 'Food & Beverage', 'Beauty'];

        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'stock' => fake()->numberBetween(0, 500),
            'category' => fake()->randomElement($categories),
            'sku' => strtoupper(fake()->bothify('PRD-####-????')),
            'image' => fake()->imageUrl(640, 480, 'products', true),
            'is_active' => fake()->boolean(90),
        ];
    }
}
