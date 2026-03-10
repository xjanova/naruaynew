<?php

namespace App\Http\Controllers;

use App\Models\LegDetail;
use App\Models\Setting;
use App\Models\SponsorTreePath;
use App\Models\TreePath;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class SetupController extends Controller
{
    public function index()
    {
        $status = [
            'database' => $this->checkDatabase(),
            'migrations' => $this->checkMigrations(),
            'seeded' => $this->checkSeeded(),
        ];

        return Inertia::render('Setup/Index', [
            'status' => $status,
        ]);
    }

    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            return back()->with('success', 'Migrations completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }

    public function runSeeders()
    {
        try {
            // Run all seeders except AdminSeeder (admin will be created via wizard)
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\CountrySeeder',
                '--force' => true,
            ]);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\RankSeeder',
                '--force' => true,
            ]);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\SettingSeeder',
                '--force' => true,
            ]);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ProductSeeder',
                '--force' => true,
            ]);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\CompensationSeeder',
                '--force' => true,
            ]);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\LevelCommissionSeeder',
                '--force' => true,
            ]);
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\PaymentGatewaySeeder',
                '--force' => true,
            ]);

            return back()->with('success', 'Database seeded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Seeding failed: ' . $e->getMessage());
        }
    }

    public function createAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'company_name' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $admin = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'transaction_password' => Hash::make('123456'),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'active_status' => 'active',
                'kyc_status' => 'approved',
                'user_level' => 0,
                'personal_pv' => 0,
                'group_pv' => 0,
                'join_date' => now(),
                'email_verified_at' => now(),
            ]);

            UserBalance::create([
                'user_id' => $admin->id,
                'balance_amount' => 0,
                'purchase_wallet' => 0,
            ]);

            UserProfile::create([
                'user_id' => $admin->id,
                'country' => 'TH',
            ]);

            // Root node in trees
            TreePath::create([
                'ancestor' => $admin->id,
                'descendant' => $admin->id,
                'depth' => 0,
            ]);
            SponsorTreePath::create([
                'ancestor' => $admin->id,
                'descendant' => $admin->id,
                'depth' => 0,
            ]);

            LegDetail::create([
                'user_id' => $admin->id,
            ]);

            // Update company name if provided
            if ($request->company_name) {
                Setting::updateOrCreate(
                    ['key' => 'company_name'],
                    ['value' => $request->company_name, 'group' => 'company']
                );
            }
        });

        // Log in the new admin
        auth()->loginUsingId(User::where('username', $request->username)->first()->id);

        return redirect('/admin/dashboard')->with('success', 'Admin account created! Welcome to Naruay MLM.');
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkMigrations(): bool
    {
        try {
            return Schema::hasTable('users') && Schema::hasTable('settings');
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkSeeded(): bool
    {
        try {
            return Setting::count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
