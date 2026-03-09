<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'name' => ucwords($name),
            'sku' => strtoupper(Str::slug($name, '-')) . '-' . fake()->unique()->numberBetween(100, 999),
            'type' => 'registration',
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 500, 15000),
            'pv_value' => fake()->randomFloat(2, 50, 1500),
            'bv_value' => fake()->randomFloat(2, 50, 1500),
            'product_validity_days' => 365,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function repurchase(): static
    {
        return $this->state(fn () => ['type' => 'repurchase']);
    }

    public function starter(): static
    {
        return $this->state(fn () => [
            'name' => 'Starter Package',
            'sku' => 'STARTER-PKG',
            'type' => 'registration',
            'price' => 1500.00,
            'pv_value' => 150.00,
            'bv_value' => 150.00,
        ]);
    }

    public function premium(): static
    {
        return $this->state(fn () => [
            'name' => 'Premium Package',
            'sku' => 'PREMIUM-PKG',
            'type' => 'registration',
            'price' => 6000.00,
            'pv_value' => 600.00,
            'bv_value' => 600.00,
        ]);
    }
}
