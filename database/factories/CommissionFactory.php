<?php

namespace Database\Factories;

use App\Models\Commission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    protected $model = Commission::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 10, 5000);
        $tds = round($amount * 0.05, 2);
        $serviceCharge = round($amount * 0.10, 2);

        return [
            'user_id' => User::factory(),
            'from_user_id' => User::factory(),
            'amount_type' => fake()->randomElement([
                'level_commission', 'binary_commission', 'matching_bonus',
                'rank_bonus', 'sales_commission', 'referral',
            ]),
            'amount' => $amount,
            'tds' => $tds,
            'service_charge' => $serviceCharge,
            'amount_payable' => $amount - $tds - $serviceCharge,
            'level' => fake()->numberBetween(1, 10),
            'transaction_id' => 'TXN-' . strtoupper(fake()->unique()->bothify('########')),
        ];
    }

    public function levelCommission(): static
    {
        return $this->state(fn () => ['amount_type' => 'level_commission']);
    }

    public function binaryCommission(): static
    {
        return $this->state(fn () => ['amount_type' => 'binary_commission']);
    }
}
