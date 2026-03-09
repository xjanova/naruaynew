<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $regCategory = ProductCategory::updateOrCreate(
            ['slug' => 'registration-packages'],
            ['name' => 'Registration Packages', 'description' => 'Packages for new member registration', 'sort_order' => 1, 'is_active' => true]
        );

        $repurchaseCategory = ProductCategory::updateOrCreate(
            ['slug' => 'repurchase-products'],
            ['name' => 'Repurchase Products', 'description' => 'Products for monthly repurchase', 'sort_order' => 2, 'is_active' => true]
        );

        $upgradeCategory = ProductCategory::updateOrCreate(
            ['slug' => 'upgrade-packages'],
            ['name' => 'Upgrade Packages', 'description' => 'Packages for upgrading membership', 'sort_order' => 3, 'is_active' => true]
        );

        // Registration Packages
        $packages = [
            ['name' => 'Starter Package',    'sku' => 'REG-STARTER',     'price' => 1500, 'pv_value' => 150, 'bv_value' => 150, 'validity' => 365, 'category' => $regCategory->id],
            ['name' => 'Basic Package',      'sku' => 'REG-BASIC',       'price' => 3000, 'pv_value' => 300, 'bv_value' => 300, 'validity' => 365, 'category' => $regCategory->id],
            ['name' => 'Silver Package',     'sku' => 'REG-SILVER',      'price' => 5000, 'pv_value' => 500, 'bv_value' => 500, 'validity' => 365, 'category' => $regCategory->id],
            ['name' => 'Gold Package',       'sku' => 'REG-GOLD',        'price' => 8000, 'pv_value' => 800, 'bv_value' => 800, 'validity' => 365, 'category' => $regCategory->id],
            ['name' => 'Premium Package',    'sku' => 'REG-PREMIUM',     'price' => 15000,'pv_value' => 1500,'bv_value' => 1500,'validity' => 365, 'category' => $regCategory->id],
            ['name' => 'BlueDiamond Package','sku' => 'REG-BLUEDIAMOND', 'price' => 60000,'pv_value' => 6000,'bv_value' => 6000,'validity' => 365, 'category' => $regCategory->id],
        ];

        foreach ($packages as $pkg) {
            Product::updateOrCreate(
                ['sku' => $pkg['sku']],
                [
                    'category_id' => $pkg['category'],
                    'name' => $pkg['name'],
                    'sku' => $pkg['sku'],
                    'type' => 'registration',
                    'description' => $pkg['name'] . ' for member registration',
                    'price' => $pkg['price'],
                    'pv_value' => $pkg['pv_value'],
                    'bv_value' => $pkg['bv_value'],
                    'product_validity_days' => $pkg['validity'],
                    'is_active' => true,
                ]
            );
        }

        // Repurchase products
        $repurchaseProducts = [
            ['name' => 'Monthly Repurchase 500',  'sku' => 'REP-500',  'price' => 500,  'pv' => 50,  'bv' => 50],
            ['name' => 'Monthly Repurchase 1000', 'sku' => 'REP-1000', 'price' => 1000, 'pv' => 100, 'bv' => 100],
            ['name' => 'Monthly Repurchase 2000', 'sku' => 'REP-2000', 'price' => 2000, 'pv' => 200, 'bv' => 200],
        ];

        foreach ($repurchaseProducts as $rp) {
            Product::updateOrCreate(
                ['sku' => $rp['sku']],
                [
                    'category_id' => $repurchaseCategory->id,
                    'name' => $rp['name'],
                    'sku' => $rp['sku'],
                    'type' => 'repurchase',
                    'description' => $rp['name'],
                    'price' => $rp['price'],
                    'pv_value' => $rp['pv'],
                    'bv_value' => $rp['bv'],
                    'product_validity_days' => 30,
                    'is_active' => true,
                ]
            );
        }
    }
}
