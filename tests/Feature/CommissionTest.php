<?php

use App\Models\User;
use App\Models\UserBalance;
use App\Models\Commission;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Services\CommissionService;

beforeEach(function () {
    $this->commissionService = app(CommissionService::class);

    // Setup TDS and service charge settings
    Setting::create(['key' => 'tds', 'value' => '5', 'group' => 'commission']);
    Setting::create(['key' => 'service_charge', 'value' => '10', 'group' => 'commission']);
});

test('commission is credited with TDS and service charge deduction', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $fromUser = User::factory()->create();

    $commission = $this->commissionService->creditCommission(
        user: $user,
        amount: 1000,
        amountType: 'level_commission',
        fromUser: $fromUser,
        level: 1,
        note: 'Test commission',
    );

    // Check commission record
    expect($commission)->not->toBeNull();
    expect((float) $commission->amount)->toBe(1000.0);
    expect((float) $commission->tds)->toBe(50.0);           // 5% TDS
    expect((float) $commission->service_charge)->toBe(100.0); // 10% service charge
    expect((float) $commission->amount_payable)->toBe(850.0); // 1000 - 50 - 100

    // Check wallet credited with payable amount
    $balance = UserBalance::where('user_id', $user->id)->first();
    expect((float) $balance->balance_amount)->toBe(850.0);
});

test('commission creates wallet transaction', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $fromUser = User::factory()->create();

    $this->commissionService->creditCommission(
        user: $user,
        amount: 1000,
        amountType: 'level_commission',
        fromUser: $fromUser,
    );

    $txn = WalletTransaction::where('user_id', $user->id)->first();
    expect($txn)->not->toBeNull();
    expect($txn->type)->toBe('credit');
    expect((float) $txn->amount)->toBe(850.0); // Payable amount after deductions
    expect($txn->amount_type)->toBe('level_commission');
});

test('zero amount commission is not created', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $fromUser = User::factory()->create();

    $result = $this->commissionService->creditCommission(
        user: $user,
        amount: 0,
        amountType: 'level_commission',
        fromUser: $fromUser,
    );

    expect($result)->toBeNull();
    expect(Commission::where('user_id', $user->id)->count())->toBe(0);
});

test('negative amount commission is not created', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $result = $this->commissionService->creditCommission(
        user: $user,
        amount: -500,
        amountType: 'level_commission',
    );

    expect($result)->toBeNull();
    expect(Commission::where('user_id', $user->id)->count())->toBe(0);
});

test('commission records from_user_id correctly', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $fromUser = User::factory()->create();

    $commission = $this->commissionService->creditCommission(
        user: $user,
        amount: 500,
        amountType: 'level_commission',
        fromUser: $fromUser,
    );

    expect($commission->from_user_id)->toBe($fromUser->id);
});

test('commission without from_user has null from_user_id', function () {
    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $commission = $this->commissionService->creditCommission(
        user: $user,
        amount: 500,
        amountType: 'rank_bonus',
    );

    expect($commission->from_user_id)->toBeNull();
});

test('commission with zero TDS and service charge credits full amount', function () {
    // Override settings to 0
    Setting::where('key', 'tds')->update(['value' => '0']);
    Setting::where('key', 'service_charge')->update(['value' => '0']);
    \Illuminate\Support\Facades\Cache::forget('settings');

    $user = User::factory()->create();
    UserBalance::create(['user_id' => $user->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);

    $commission = $this->commissionService->creditCommission(
        user: $user,
        amount: 1000,
        amountType: 'level_commission',
    );

    expect((float) $commission->amount_payable)->toBe(1000.0);

    $balance = UserBalance::where('user_id', $user->id)->first();
    expect((float) $balance->balance_amount)->toBe(1000.0);
});

test('getSponsorUplines returns correct upline chain', function () {
    $root = User::factory()->create();
    $level1 = User::factory()->create(['sponsor_id' => $root->id]);
    $level2 = User::factory()->create(['sponsor_id' => $level1->id]);
    $level3 = User::factory()->create(['sponsor_id' => $level2->id]);

    $uplines = $this->commissionService->getSponsorUplines($level3, 3);

    expect($uplines)->toHaveCount(3);
    expect($uplines[1]->id)->toBe($level2->id);
    expect($uplines[2]->id)->toBe($level1->id);
    expect($uplines[3]->id)->toBe($root->id);
});

test('getSponsorUplines respects max levels', function () {
    $root = User::factory()->create();
    $level1 = User::factory()->create(['sponsor_id' => $root->id]);
    $level2 = User::factory()->create(['sponsor_id' => $level1->id]);
    $level3 = User::factory()->create(['sponsor_id' => $level2->id]);

    $uplines = $this->commissionService->getSponsorUplines($level3, 1);

    expect($uplines)->toHaveCount(1);
    expect($uplines[1]->id)->toBe($level2->id);
});

test('shouldSkipUser returns true for inactive user when setting enabled', function () {
    Setting::create(['key' => 'skip_blocked_users_commission', 'value' => 'yes', 'group' => 'commission']);
    \Illuminate\Support\Facades\Cache::forget('settings');

    $user = User::factory()->inactive()->create();

    expect($this->commissionService->shouldSkipUser($user))->toBeTrue();
});

test('shouldSkipUser returns false for active user', function () {
    Setting::create(['key' => 'skip_blocked_users_commission', 'value' => 'yes', 'group' => 'commission']);
    \Illuminate\Support\Facades\Cache::forget('settings');

    $user = User::factory()->create(); // active by default

    expect($this->commissionService->shouldSkipUser($user))->toBeFalse();
});
