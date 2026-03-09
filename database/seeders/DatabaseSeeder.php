<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            RankSeeder::class,
            SettingSeeder::class,
            ProductSeeder::class,
            CompensationSeeder::class,
            LevelCommissionSeeder::class,
            PaymentGatewaySeeder::class,
            AdminSeeder::class,
        ]);
    }
}
