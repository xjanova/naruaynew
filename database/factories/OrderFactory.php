<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper(fake()->unique()->bothify('####??')),
            'type' => fake()->randomElement(['registration', 'repurchase', 'upgrade']),
            'total_amount' => fake()->randomFloat(2, 500, 15000),
            'total_pv' => fake()->randomFloat(2, 50, 1500),
            'total_bv' => fake()->randomFloat(2, 50, 1500),
            'payment_method' => fake()->randomElement(['ewallet', 'bank_transfer', 'epin']),
            'payment_status' => 'completed',
            'order_status' => 'completed',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);
    }

    public function registration(): static
    {
        return $this->state(fn () => ['type' => 'registration']);
    }

    public function repurchase(): static
    {
        return $this->state(fn () => ['type' => 'repurchase']);
    }
}
