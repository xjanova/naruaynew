<?php

namespace Database\Factories;

use App\Models\PayoutRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayoutRequestFactory extends Factory
{
    protected $model = PayoutRequest::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 500, 50000);
        $fee = round($amount * 0.05, 2);

        return [
            'user_id' => User::factory(),
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $amount - $fee,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }
}
