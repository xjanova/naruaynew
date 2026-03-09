<?php

namespace App\Services;

use App\Models\LevelCommissionByPackage;
use App\Models\LevelCommissionByRank;
use App\Models\LevelCommissionConfig;
use App\Models\Product;
use App\Models\User;

class LevelCommissionService
{
    public function __construct(
        private CommissionService $commissionService,
    ) {}

    /**
     * Calculate level commission for sponsor upline chain
     */
    public function calculate(User $buyer, Product $product, string $orderType = 'registration'): void
    {
        $depthCeiling = (int) setting('commission_upto_level', 10);
        $criteria = setting('commission_criteria', 'genealogy');
        $commissionType = setting('level_commission_type', 'Percentage');

        $amountType = match ($orderType) {
            'repurchase' => 'repurchase_level_commission',
            'upgrade' => 'upgrade_level_commission',
            default => 'level_commission',
        };

        $uplines = $this->commissionService->getSponsorUplines($buyer, $depthCeiling);
        $compressedAmount = 0;

        foreach ($uplines as $level => $upline) {
            // Skip blocked/inactive users -> compress commission
            if ($this->commissionService->shouldSkipUser($upline)) {
                $levelPercent = $this->getLevelPercent($level, $criteria, $product, $upline);
                $amount = $this->calculateAmount($commissionType, $product, $levelPercent);
                $compressedAmount += $amount;
                continue;
            }

            $levelPercent = $this->getLevelPercent($level, $criteria, $product, $upline);
            $amount = $this->calculateAmount($commissionType, $product, $levelPercent);

            // Add compressed commission from skipped users
            $amount += $compressedAmount;
            $compressedAmount = 0;

            if ($amount > 0) {
                $this->commissionService->creditCommission(
                    user: $upline,
                    amount: $amount,
                    amountType: $amountType,
                    fromUser: $buyer,
                    product: $product,
                    level: $level,
                    note: "Level {$level} commission from {$buyer->username}",
                );
            }
        }
    }

    private function getLevelPercent(int $level, string $criteria, Product $product, User $upline): float
    {
        return match ($criteria) {
            'reg_pck' => LevelCommissionByPackage::where('level', $level)
                ->where('product_id', $product->id)
                ->value('commission_reg_pck') ?? 0,
            'member_pck' => LevelCommissionByPackage::where('level', $level)
                ->where('product_id', $upline->product_id)
                ->value('commission_member_pck') ?? 0,
            'rank' => LevelCommissionByRank::where('level', $level)
                ->where('rank_id', $upline->rank_id)
                ->value('commission') ?? 0,
            default => LevelCommissionConfig::where('level', $level)
                ->value('percentage') ?? 0,
        };
    }

    private function calculateAmount(string $type, Product $product, float $levelPercent): float
    {
        if ($type === 'Percentage') {
            return $product->pv_value * ($levelPercent / 100);
        }
        return $levelPercent; // Fixed amount
    }
}
