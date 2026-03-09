<?php

namespace Database\Seeders;

use App\Models\Compensation;
use App\Models\ModuleStatus;
use Illuminate\Database\Seeder;

class CompensationSeeder extends Seeder
{
    public function run(): void
    {
        $compensations = [
            ['name' => 'level_commission',     'display_name' => 'Level Commission',     'is_enabled' => true,  'config' => ['criteria' => 'by_percentage', 'levels' => 10]],
            ['name' => 'binary_commission',    'display_name' => 'Binary Commission',    'is_enabled' => true,  'config' => ['type' => '1:1', 'pair_value' => 10, 'capping' => 50000]],
            ['name' => 'matching_bonus',       'display_name' => 'Matching Bonus',       'is_enabled' => true,  'config' => ['levels' => 5]],
            ['name' => 'rank_bonus',           'display_name' => 'Rank Bonus',           'is_enabled' => true,  'config' => []],
            ['name' => 'sales_commission',     'display_name' => 'Sales Commission',     'is_enabled' => true,  'config' => ['split' => 'cv_sp']],
            ['name' => 'referral',             'display_name' => 'Referral Commission',  'is_enabled' => true,  'config' => ['percentage' => 10]],
            ['name' => 'performance_bonus',    'display_name' => 'Performance Bonus',    'is_enabled' => false, 'config' => []],
            ['name' => 'fast_start_bonus',     'display_name' => 'Fast Start Bonus',     'is_enabled' => false, 'config' => []],
            ['name' => 'pool_bonus',           'display_name' => 'Pool Bonus',           'is_enabled' => false, 'config' => []],
            ['name' => 'xup_commission',       'display_name' => 'X-UP Commission',      'is_enabled' => false, 'config' => ['modulo' => 3]],
            ['name' => 'repurchase_level_commission', 'display_name' => 'Repurchase Level Commission', 'is_enabled' => true, 'config' => ['levels' => 10]],
            ['name' => 'upgrade_level_commission',    'display_name' => 'Upgrade Level Commission',    'is_enabled' => true, 'config' => ['levels' => 10]],
        ];

        foreach ($compensations as $comp) {
            Compensation::updateOrCreate(
                ['name' => $comp['name']],
                $comp
            );
        }

        // Module statuses
        $modules = [
            ['module_name' => 'binary_tree',       'is_enabled' => true],
            ['module_name' => 'sponsor_tree',      'is_enabled' => true],
            ['module_name' => 'ewallet',           'is_enabled' => true],
            ['module_name' => 'fund_transfer',     'is_enabled' => true],
            ['module_name' => 'payout',            'is_enabled' => true],
            ['module_name' => 'epin',              'is_enabled' => true],
            ['module_name' => 'kyc',               'is_enabled' => false],
            ['module_name' => 'google2fa',         'is_enabled' => false],
            ['module_name' => 'subscription',      'is_enabled' => true],
        ];

        foreach ($modules as $module) {
            ModuleStatus::updateOrCreate(
                ['module_name' => $module['module_name']],
                array_merge($module, ['config' => []])
            );
        }
    }
}
