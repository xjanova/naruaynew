<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Company
            ['key' => 'company_name', 'value' => 'Naruay MLM', 'group' => 'company'],
            ['key' => 'company_email', 'value' => 'info@naruay.com', 'group' => 'company'],
            ['key' => 'company_phone', 'value' => '+66-XX-XXXX-XXXX', 'group' => 'company'],
            ['key' => 'company_address', 'value' => 'Bangkok, Thailand', 'group' => 'company'],
            ['key' => 'company_logo', 'value' => '/images/logo.png', 'group' => 'company'],

            // Currency
            ['key' => 'default_currency', 'value' => 'THB', 'group' => 'currency'],
            ['key' => 'currency_symbol', 'value' => '฿', 'group' => 'currency'],
            ['key' => 'currency_position', 'value' => 'before', 'group' => 'currency'],

            // Commission
            ['key' => 'tds_percentage', 'value' => '5', 'group' => 'commission'],
            ['key' => 'service_charge_percentage', 'value' => '10', 'group' => 'commission'],
            ['key' => 'commission_status', 'value' => 'enabled', 'group' => 'commission'],

            // Payout
            ['key' => 'min_payout_amount', 'value' => '500', 'group' => 'payout'],
            ['key' => 'max_payout_amount', 'value' => '100000', 'group' => 'payout'],
            ['key' => 'payout_fee_percentage', 'value' => '5', 'group' => 'payout'],
            ['key' => 'payout_day', 'value' => 'weekly', 'group' => 'payout'],

            // Registration
            ['key' => 'registration_type', 'value' => 'epin_and_ewallet', 'group' => 'registration'],
            ['key' => 'default_position', 'value' => 'auto', 'group' => 'registration'],
            ['key' => 'binary_plan', 'value' => 'yes', 'group' => 'registration'],

            // Fund Transfer
            ['key' => 'fund_transfer_status', 'value' => 'enabled', 'group' => 'fund_transfer'],
            ['key' => 'fund_transfer_fee', 'value' => '0', 'group' => 'fund_transfer'],
            ['key' => 'min_transfer_amount', 'value' => '100', 'group' => 'fund_transfer'],

            // KYC
            ['key' => 'kyc_mandatory', 'value' => 'no', 'group' => 'kyc'],

            // Subscription
            ['key' => 'subscription_status', 'value' => 'enabled', 'group' => 'subscription'],

            // Binary
            ['key' => 'binary_type', 'value' => '1:1', 'group' => 'binary'],
            ['key' => 'binary_pair_value', 'value' => '10', 'group' => 'binary'],
            ['key' => 'binary_capping', 'value' => '50000', 'group' => 'binary'],
            ['key' => 'carry_forward', 'value' => 'yes', 'group' => 'binary'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
