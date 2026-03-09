<?php

namespace Database\Seeders;

use App\Models\LevelCommissionConfig;
use Illuminate\Database\Seeder;

class LevelCommissionSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['level' => 1,  'percentage' => 10.00],
            ['level' => 2,  'percentage' => 7.00],
            ['level' => 3,  'percentage' => 5.00],
            ['level' => 4,  'percentage' => 4.00],
            ['level' => 5,  'percentage' => 3.00],
            ['level' => 6,  'percentage' => 2.00],
            ['level' => 7,  'percentage' => 1.50],
            ['level' => 8,  'percentage' => 1.00],
            ['level' => 9,  'percentage' => 0.75],
            ['level' => 10, 'percentage' => 0.50],
        ];

        foreach ($levels as $level) {
            LevelCommissionConfig::updateOrCreate(
                ['level' => $level['level']],
                $level
            );
        }
    }
}
