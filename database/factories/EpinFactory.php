<?php

namespace Database\Factories;

use App\Models\Epin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EpinFactory extends Factory
{
    protected $model = Epin::class;

    public function definition(): array
    {
        return [
            'pin_number' => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
            'amount' => fake()->randomFloat(2, 500, 15000),
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ];
    }

    public function used(): static
    {
        return $this->state(fn () => [
            'status' => 'used',
            'used_at' => now(),
        ]);
    }
}
