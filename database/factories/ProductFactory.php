<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 5, 500),
            'stock' => fake()->numberBetween(20, 200),
            'minimum_stock' => 10,
            'expires_at' => null,
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 2,
            'minimum_stock' => 10,
        ]);
    }

    public function expiringIn(int $days): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => today()->addDays($days),
        ]);
    }
}
