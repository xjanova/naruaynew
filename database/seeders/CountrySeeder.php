<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['name' => 'Thailand',      'code' => 'TH', 'phone_code' => '+66',  'status' => true],
            ['name' => 'India',         'code' => 'IN', 'phone_code' => '+91',  'status' => true],
            ['name' => 'United States', 'code' => 'US', 'phone_code' => '+1',   'status' => true],
            ['name' => 'Malaysia',      'code' => 'MY', 'phone_code' => '+60',  'status' => true],
            ['name' => 'Singapore',     'code' => 'SG', 'phone_code' => '+65',  'status' => true],
            ['name' => 'Philippines',   'code' => 'PH', 'phone_code' => '+63',  'status' => true],
            ['name' => 'Indonesia',     'code' => 'ID', 'phone_code' => '+62',  'status' => true],
            ['name' => 'Myanmar',       'code' => 'MM', 'phone_code' => '+95',  'status' => true],
            ['name' => 'Vietnam',       'code' => 'VN', 'phone_code' => '+84',  'status' => true],
            ['name' => 'Cambodia',      'code' => 'KH', 'phone_code' => '+855', 'status' => true],
            ['name' => 'Laos',          'code' => 'LA', 'phone_code' => '+856', 'status' => true],
            ['name' => 'Japan',         'code' => 'JP', 'phone_code' => '+81',  'status' => true],
            ['name' => 'South Korea',   'code' => 'KR', 'phone_code' => '+82',  'status' => true],
            ['name' => 'China',         'code' => 'CN', 'phone_code' => '+86',  'status' => true],
            ['name' => 'United Kingdom','code' => 'GB', 'phone_code' => '+44',  'status' => true],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(['code' => $country['code']], $country);
        }

        // Currencies
        $currencies = [
            ['name' => 'Thai Baht',    'code' => 'THB', 'symbol' => '฿',  'exchange_rate' => 1.00, 'is_default' => true,  'status' => true],
            ['name' => 'Indian Rupee', 'code' => 'INR', 'symbol' => '₹',  'exchange_rate' => 0.85, 'is_default' => false, 'status' => true],
            ['name' => 'US Dollar',    'code' => 'USD', 'symbol' => '$',  'exchange_rate' => 0.029,'is_default' => false, 'status' => true],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }

        // Languages
        $languages = [
            ['name' => 'Thai',    'code' => 'th', 'flag' => '🇹🇭', 'is_default' => true,  'status' => true],
            ['name' => 'English', 'code' => 'en', 'flag' => '🇺🇸', 'is_default' => false, 'status' => true],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(['code' => $language['code']], $language);
        }
    }
}
