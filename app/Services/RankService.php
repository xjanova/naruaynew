<?php

namespace App\Services;

use App\Models\DownlineRankConfig;
use App\Models\PurchaseRankConfig;
use App\Models\Rank;
use App\Models\RankHistory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RankService
{
    public function __construct(
        private CommissionService $commissionService,
    ) {}

    public function checkAndUpdateRank(User $user): bool
    {
        $ranks = Cache::tags(['rank'])->remember('all_ranks', 3600, function () {
            return Rank::where('is_active', true)->orderBy('id', 'desc')->get();
        });

        foreach ($ranks as $rank) {
            if ($rank->id <= $user->rank_id) continue;

            if ($this->meetsAllCriteria($user, $rank)) {
                $oldRankId = $user->rank_id;
                $user->update(['rank_id' => $rank->id]);

                RankHistory::create([
                    'user_id' => $user->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $rank->id,
                ]);

                // Pay rank bonus if enabled
                if (SettingService::getBool('rank_commission_status')) {
                    $this->payRankBonus($user, $rank);
                }

                Cache::tags(["user:{$user->id}"])->flush();
                return true;
            }
        }

        return false;
    }

    private function meetsAllCriteria(User $user, Rank $rank): bool
    {
        // Referral count
        if ($rank->referral_count > 0) {
            if ($user->directReferrals()->where('active_status', 'active')->count() < $rank->referral_count) {
                return false;
            }
        }

        // Personal PV
        if ($rank->personal_pv > 0 && $user->personal_pv < $rank->personal_pv) {
            return false;
        }

        // Group PV
        if ($rank->group_pv > 0 && $user->group_pv < $rank->group_pv) {
            return false;
        }

        // Downline count
        if ($rank->downline_count > 0) {
            $count = \App\Models\TreePath::where('ancestor', $user->id)
                ->where('descendant', '!=', $user->id)
                ->count();
            if ($count < $rank->downline_count) {
                return false;
            }
        }

        // Downline rank requirements
        $downlineRankConfigs = DownlineRankConfig::where('rank_id', $rank->id)->get();
        foreach ($downlineRankConfigs as $config) {
            $qualifiedCount = User::where('sponsor_id', $user->id)
                ->where('rank_id', '>=', $config->required_rank_id)
                ->where('active_status', 'active')
                ->count();
            if ($qualifiedCount < $config->required_count) {
                return false;
            }
        }

        // Purchase rank requirements
        $purchaseConfigs = PurchaseRankConfig::where('rank_id', $rank->id)->get();
        foreach ($purchaseConfigs as $config) {
            $purchaseCount = \App\Models\OrderItem::whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('order_status', 'completed');
            })->where('product_id', $config->product_id)->sum('quantity');
            if ($purchaseCount < $config->required_count) {
                return false;
            }
        }

        return true;
    }

    private function payRankBonus(User $user, Rank $rank): void
    {
        if ($rank->rank_bonus <= 0) return;

        $this->commissionService->creditCommission(
            user: $user,
            amount: (float) $rank->rank_bonus,
            amountType: 'rank_bonus',
            note: "Rank bonus for achieving {$rank->name}",
        );
    }
}
