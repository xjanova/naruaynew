<?php

use App\Models\User;
use App\Models\TreePath;
use App\Models\SponsorTreePath;
use App\Models\LegDetail;
use App\Models\UserBalance;
use App\Services\BinaryTreeService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Create admin/root user
    $this->admin = User::factory()->admin()->create();
    UserBalance::create(['user_id' => $this->admin->id, 'balance_amount' => 0, 'purchase_wallet' => 0]);
    LegDetail::create(['user_id' => $this->admin->id, 'left_count' => 0, 'right_count' => 0, 'left_active_count' => 0, 'right_active_count' => 0, 'left_pv' => 0, 'right_pv' => 0, 'left_carry' => 0, 'right_carry' => 0, 'left_total_pv' => 0, 'right_total_pv' => 0, 'total_left_count' => 0, 'total_right_count' => 0, 'total_left_active_count' => 0, 'total_right_active_count' => 0]);
    TreePath::create(['ancestor' => $this->admin->id, 'descendant' => $this->admin->id, 'depth' => 0]);
    SponsorTreePath::create(['ancestor' => $this->admin->id, 'descendant' => $this->admin->id, 'depth' => 0]);

    $this->treeService = app(BinaryTreeService::class);
});

test('findPlacement returns left position when left is available', function () {
    $result = $this->treeService->findPlacement($this->admin->id, 'L');

    expect($result['placement_id'])->toBe($this->admin->id);
    expect($result['position'])->toBe('L');
});

test('findPlacement returns right position when right is available', function () {
    $result = $this->treeService->findPlacement($this->admin->id, 'R');

    expect($result['placement_id'])->toBe($this->admin->id);
    expect($result['position'])->toBe('R');
});

test('addToTree creates tree paths for new member', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);

    $this->treeService->addToTree($member);

    // Check placement tree paths created (self + parent)
    $paths = TreePath::where('descendant', $member->id)->get();
    expect($paths)->toHaveCount(2);

    // Self-reference path
    $selfPath = $paths->where('ancestor', $member->id)->first();
    expect($selfPath)->not->toBeNull();

    // Parent path
    $parentPath = $paths->where('ancestor', $this->admin->id)->first();
    expect($parentPath)->not->toBeNull();
});

test('addToTree creates sponsor tree paths for new member', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);

    $this->treeService->addToTree($member);

    $sponsorPaths = SponsorTreePath::where('descendant', $member->id)->get();
    expect($sponsorPaths)->toHaveCount(2); // self + sponsor

    $sponsorPath = $sponsorPaths->where('ancestor', $this->admin->id)->first();
    expect($sponsorPath)->not->toBeNull();
});

test('addToTree initializes leg detail for new member', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);

    $this->treeService->addToTree($member);

    $legDetail = LegDetail::where('user_id', $member->id)->first();
    expect($legDetail)->not->toBeNull();
});

test('addToTree updates upline leg counts on left placement', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);

    $this->treeService->addToTree($member);

    $adminLeg = LegDetail::where('user_id', $this->admin->id)->first();
    expect($adminLeg->total_left_count)->toBeGreaterThanOrEqual(1);
});

test('addToTree updates upline leg counts on right placement', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'R',
    ]);

    $this->treeService->addToTree($member);

    $adminLeg = LegDetail::where('user_id', $this->admin->id)->first();
    expect($adminLeg->total_right_count)->toBeGreaterThanOrEqual(1);
});

test('findPlacement uses BFS when preferred position is occupied', function () {
    // Place first member on left
    $member1 = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);
    $this->treeService->addToTree($member1);

    // Now try to find placement on left again - should go under member1
    $result = $this->treeService->findPlacement($this->admin->id, 'L');

    // Since admin's left is taken by member1, BFS finds member1's left
    expect($result['placement_id'])->toBe($member1->id);
    expect($result['position'])->toBe('L');
});

test('findPlacement with both sides occupied uses BFS deeper', function () {
    // Place left child
    $member1 = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);
    $this->treeService->addToTree($member1);

    // Place right child
    $member2 = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'R',
    ]);
    $this->treeService->addToTree($member2);

    // Find left placement - both admin slots taken, should go under member1 (BFS)
    $result = $this->treeService->findPlacement($this->admin->id, 'L');
    expect($result['placement_id'])->toBe($member1->id);
    expect($result['position'])->toBe('L');
});

test('determineLegSide returns correct side for left child', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);
    $this->treeService->addToTree($member);

    $side = $this->treeService->determineLegSide($this->admin->id, $member->id);
    expect($side)->toBe('L');
});

test('determineLegSide returns correct side for right child', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'R',
    ]);
    $this->treeService->addToTree($member);

    $side = $this->treeService->determineLegSide($this->admin->id, $member->id);
    expect($side)->toBe('R');
});

test('getTreeData returns structured tree data', function () {
    $member = User::factory()->create([
        'sponsor_id' => $this->admin->id,
        'placement_id' => $this->admin->id,
        'position' => 'L',
    ]);
    $this->treeService->addToTree($member);

    $treeData = $this->treeService->getTreeData($this->admin->id, 2);

    expect($treeData)->not->toBeNull();
    expect($treeData['id'])->toBe($this->admin->id);
    expect($treeData['children'])->toHaveKeys(['left', 'right']);
    expect($treeData['children']['left'])->not->toBeNull();
    expect($treeData['children']['left']['id'])->toBe($member->id);
    expect($treeData['children']['right'])->toBeNull();
});

test('checkAutoRankPromotion promotes user with 6000+ PV order', function () {
    $user = User::factory()->create(['rank_id' => 1]);

    $result = $this->treeService->checkAutoRankPromotion($user, 6000);

    expect($result)->toBeTrue();
    expect($user->fresh()->rank_id)->toBe(11);
});

test('checkAutoRankPromotion does not promote user with less than 6000 PV', function () {
    $user = User::factory()->create(['rank_id' => 1]);

    $result = $this->treeService->checkAutoRankPromotion($user, 5999);

    expect($result)->toBeFalse();
    expect($user->fresh()->rank_id)->toBe(1);
});

test('checkAutoRankPromotion does not promote user already at rank 11 or above', function () {
    $user = User::factory()->create(['rank_id' => 11]);

    $result = $this->treeService->checkAutoRankPromotion($user, 7000);

    expect($result)->toBeFalse();
    expect($user->fresh()->rank_id)->toBe(11);
});
