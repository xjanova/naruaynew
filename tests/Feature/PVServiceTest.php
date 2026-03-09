<?php

use App\Models\User;
use App\Models\UserBalance;
use App\Models\LegDetail;
use App\Models\TreePath;
use App\Models\SponsorTreePath;
use App\Models\PvHistory;
use App\Services\PVService;
use App\Services\BinaryTreeService;

beforeEach(function () {
    $this->pvService = app(PVService::class);
    $this->treeService = app(BinaryTreeService::class);

    $this->admin = User::factory()->admin()->create();
    UserBalance::create(['user_id' => $this->admin->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);
    LegDetail::create(['user_id' => $this->admin->id, 'left_count' => 0, 'right_count' => 0, 'left_active_count' => 0, 'right_active_count' => 0, 'left_pv' => 0, 'right_pv' => 0, 'left_carry' => 0, 'right_carry' => 0, 'left_total_pv' => 0, 'right_total_pv' => 0, 'total_left_count' => 0, 'total_right_count' => 0, 'total_left_active_count' => 0, 'total_right_active_count' => 0]);
    TreePath::create(['ancestor' => $this->admin->id, 'descendant' => $this->admin->id, 'depth' => 0]);
    SponsorTreePath::create(['ancestor' => $this->admin->id, 'descendant' => $this->admin->id, 'depth' => 0]);
});

test('personal PV is updated correctly', function () {
    $user = User::factory()->create(['personal_pv' => 0]);

    $this->pvService->updatePersonalPV($user, 500, 'purchase');

    expect((float) $user->fresh()->personal_pv)->toBe(500.00);
});

test('personal PV accumulates on multiple updates', function () {
    $user = User::factory()->create(['personal_pv' => 200]);

    $this->pvService->updatePersonalPV($user, 300, 'purchase');

    expect((float) $user->fresh()->personal_pv)->toBe(500.00);
});

test('personal PV update creates PV history record', function () {
    $user = User::factory()->create(['personal_pv' => 0]);

    $this->pvService->updatePersonalPV($user, 500, 'purchase');

    $history = PvHistory::where('user_id', $user->id)->first();
    expect($history)->not->toBeNull();
    expect((float) $history->pv_amount)->toBe(500.00);
    expect($history->type)->toBe('personal');
    expect($history->source)->toBe('purchase');
});

test('personal PV update with product_id records product', function () {
    $user = User::factory()->create(['personal_pv' => 0]);

    $this->pvService->updatePersonalPV($user, 100, 'purchase', 5);

    $history = PvHistory::where('user_id', $user->id)->first();
    expect($history->product_id)->toBe(5);
});

test('propagateGroupPV updates all sponsor ancestors', function () {
    // Create a chain: admin -> member1 -> member2
    $member1 = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
        'group_pv' => 0,
    ]);
    $this->treeService->addToTree($member1);

    $member2 = User::factory()->create([
        'sponsor_id' => $member1->id,
        'placement_id' => $member1->id,
        'position' => 'L',
        'group_pv' => 0,
    ]);
    $this->treeService->addToTree($member2);

    // Propagate PV from member2
    $this->pvService->propagateGroupPV($member2, 1000);

    // Both member1 and admin should have increased group PV
    expect((float) $member1->fresh()->group_pv)->toBe(1000.00);
    expect((float) $this->admin->fresh()->group_pv)->toBe(1000.00);

    // member2's own group PV should not change (only ancestors)
    expect((float) $member2->fresh()->group_pv)->toBe(0.00);
});

test('propagateGroupPV creates PV history for each ancestor', function () {
    $member1 = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
        'group_pv' => 0,
    ]);
    $this->treeService->addToTree($member1);

    $this->pvService->propagateGroupPV($member1, 500);

    $histories = PvHistory::where('type', 'group')
        ->where('from_user_id', $member1->id)
        ->get();

    expect($histories)->toHaveCount(1); // Only admin is ancestor
    expect($histories->first()->user_id)->toBe($this->admin->id);
    expect((float) $histories->first()->pv_amount)->toBe(500.00);
    expect($histories->first()->source)->toBe('downline_purchase');
});

test('propagateGroupPV does nothing for user with no sponsor ancestors', function () {
    // User with no sponsor tree paths (except self)
    $loner = User::factory()->create(['group_pv' => 0]);
    SponsorTreePath::create(['ancestor' => $loner->id, 'descendant' => $loner->id, 'depth' => 0]);

    $this->pvService->propagateGroupPV($loner, 1000);

    // No PV history should be created for group type
    $histories = PvHistory::where('type', 'group')
        ->where('from_user_id', $loner->id)
        ->get();

    expect($histories)->toHaveCount(0);
});
