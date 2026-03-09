<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserBalance;
use App\Models\UserProfile;
use App\Models\TreePath;
use App\Models\SponsorTreePath;
use App\Models\LegDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@naruay.com',
                'password' => Hash::make('Admin@2024'),
                'transaction_password' => Hash::make('123456'),
                'first_name' => 'Admin',
                'last_name' => 'System',
                'phone' => '+66000000000',
                'active_status' => 'active',
                'kyc_status' => 'approved',
                'user_level' => 0,
                'personal_pv' => 0,
                'group_pv' => 0,
                'join_date' => now(),
                'email_verified_at' => now(),
            ]
        );

        // Create balance
        UserBalance::updateOrCreate(
            ['user_id' => $admin->id],
            ['balance_amount' => 0, 'purchase_wallet' => 0]
        );

        // Create profile
        UserProfile::updateOrCreate(
            ['user_id' => $admin->id],
            ['country' => 'TH', 'city' => 'Bangkok']
        );

        // Self-referencing tree path (root node)
        TreePath::updateOrCreate(
            ['ancestor' => $admin->id, 'descendant' => $admin->id],
            ['depth' => 0]
        );
        SponsorTreePath::updateOrCreate(
            ['ancestor' => $admin->id, 'descendant' => $admin->id],
            ['depth' => 0]
        );

        // Leg details
        LegDetail::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'total_left_count' => 0, 'total_right_count' => 0,
                'total_left_carry' => 0, 'total_right_carry' => 0,
                'total_active' => 0, 'total_inactive' => 0,
                'left_carry_forward' => 0, 'right_carry_forward' => 0,
            ]
        );
    }
}
