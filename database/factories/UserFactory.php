<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'transaction_password' => Hash::make('123456'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female']),
            'active_status' => 'active',
            'kyc_status' => 'approved',
            'personal_pv' => 0,
            'group_pv' => 0,
            'user_level' => 1,
            'join_date' => now(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'username' => 'admin',
            'email' => 'admin@naruay.com',
            'first_name' => 'Admin',
            'last_name' => 'System',
            'user_level' => 0,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['active_status' => 'inactive']);
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['active_status' => 'blocked']);
    }

    public function withPV(float $personal = 100, float $group = 500): static
    {
        return $this->state(fn () => [
            'personal_pv' => $personal,
            'group_pv' => $group,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }
}
