<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            ['name' => 'E-Wallet',       'slug' => 'ewallet',       'config' => [], 'is_active' => true],
            ['name' => 'E-PIN',          'slug' => 'epin',          'config' => [], 'is_active' => true],
            ['name' => 'Bank Transfer',  'slug' => 'bank_transfer', 'config' => ['bank_name' => '', 'account_number' => '', 'account_name' => ''], 'is_active' => true],
            ['name' => 'Free Registration','slug' => 'free',        'config' => [], 'is_active' => false],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::updateOrCreate(['slug' => $gateway['slug']], $gateway);
        }
    }
}
