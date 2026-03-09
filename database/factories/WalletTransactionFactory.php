<?php

namespace Database\Factories;

use App\Models\WalletTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'ewallet_type' => fake()->randomElement(['commission', 'fund_transfer', 'payout', 'purchase']),
            'amount' => fake()->randomFloat(2, 10, 5000),
            'purchase_wallet' => 0,
            'amount_type' => fake()->randomElement([
                'level_commission', 'binary_commission', 'fund_transfer',
                'payout', 'registration', 'purchase',
            ]),
            'type' => fake()->randomElement(['credit', 'debit']),
            'transaction_fee' => 0,
            'transaction_id' => 'WTX-' . strtoupper(fake()->unique()->bothify('########')),
        ];
    }

    public function credit(): static
    {
        return $this->state(fn () => ['type' => 'credit']);
    }

    public function debit(): static
    {
        return $this->state(fn () => ['type' => 'debit']);
    }
}
