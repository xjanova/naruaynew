<?php

namespace Database\Factories;

use App\Models\Rank;
use Illuminate\Database\Eloquent\Factories\Factory;

class RankFactory extends Factory
{
    protected $model = Rank::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Rank',
            'color' => fake()->hexColor(),
            'referral_count' => 0,
            'personal_pv' => 0,
            'group_pv' => 0,
            'downline_count' => 0,
            'team_member_count' => 0,
            'rank_bonus' => 0,
            'party_commission' => 0,
            'is_active' => true,
        ];
    }
}
