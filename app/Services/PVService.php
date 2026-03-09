<?php

namespace App\Services;

use App\Models\PvHistory;
use App\Models\SponsorTreePath;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PVService
{
    public function updatePersonalPV(User $user, float $pvAmount, string $source = 'purchase', ?int $productId = null): void
    {
        $user->increment('personal_pv', $pvAmount);

        PvHistory::create([
            'user_id' => $user->id,
            'pv_amount' => $pvAmount,
            'type' => 'personal',
            'source' => $source,
            'product_id' => $productId,
        ]);
    }

    public function propagateGroupPV(User $user, float $pvAmount): void
    {
        // Get all sponsor ancestors (excluding self)
        $ancestorIds = DB::table('sponsor_tree_paths')
            ->where('descendant', $user->id)
            ->where('ancestor', '!=', $user->id)
            ->pluck('ancestor');

        if ($ancestorIds->isEmpty()) return;

        // Bulk update group PV for all ancestors
        User::whereIn('id', $ancestorIds)->increment('group_pv', $pvAmount);

        // Log PV history for each ancestor
        $histories = $ancestorIds->map(fn($ancestorId) => [
            'user_id' => $ancestorId,
            'pv_amount' => $pvAmount,
            'type' => 'group',
            'source' => 'downline_purchase',
            'from_user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        PvHistory::insert($histories);
    }
}
