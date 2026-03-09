<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    public function run(): void
    {
        $ranks = [
            ['name' => 'Member',         'color' => '#9CA3AF', 'referral_count' => 0,  'personal_pv' => 0,    'group_pv' => 0,       'rank_bonus' => 0],
            ['name' => 'Silver',         'color' => '#C0C0C0', 'referral_count' => 2,  'personal_pv' => 100,  'group_pv' => 500,     'rank_bonus' => 500],
            ['name' => 'Gold',           'color' => '#FFD700', 'referral_count' => 3,  'personal_pv' => 200,  'group_pv' => 1500,    'rank_bonus' => 1000],
            ['name' => 'Platinum',       'color' => '#E5E4E2', 'referral_count' => 4,  'personal_pv' => 300,  'group_pv' => 5000,    'rank_bonus' => 2500],
            ['name' => 'Emerald',        'color' => '#50C878', 'referral_count' => 5,  'personal_pv' => 400,  'group_pv' => 15000,   'rank_bonus' => 5000],
            ['name' => 'Ruby',           'color' => '#E0115F', 'referral_count' => 6,  'personal_pv' => 500,  'group_pv' => 50000,   'rank_bonus' => 10000],
            ['name' => 'Sapphire',       'color' => '#0F52BA', 'referral_count' => 7,  'personal_pv' => 600,  'group_pv' => 100000,  'rank_bonus' => 25000],
            ['name' => 'Star',           'color' => '#FFB347', 'referral_count' => 8,  'personal_pv' => 700,  'group_pv' => 250000,  'rank_bonus' => 50000],
            ['name' => 'Crown',          'color' => '#B8860B', 'referral_count' => 9,  'personal_pv' => 800,  'group_pv' => 500000,  'rank_bonus' => 100000],
            ['name' => 'Diamond',        'color' => '#B9F2FF', 'referral_count' => 10, 'personal_pv' => 1000, 'group_pv' => 1000000, 'rank_bonus' => 250000],
            ['name' => 'BlueDiamond',    'color' => '#4169E1', 'referral_count' => 0,  'personal_pv' => 6000, 'group_pv' => 0,       'rank_bonus' => 500000],
            ['name' => 'CrownDiamond',   'color' => '#9400D3', 'referral_count' => 12, 'personal_pv' => 2000, 'group_pv' => 5000000, 'rank_bonus' => 1000000],
        ];

        foreach ($ranks as $rank) {
            Rank::updateOrCreate(
                ['name' => $rank['name']],
                array_merge($rank, ['is_active' => true])
            );
        }
    }
}
