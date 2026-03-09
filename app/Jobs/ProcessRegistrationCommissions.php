<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use App\Services\BinaryTreeService;
use App\Services\CommissionService;
use App\Services\LevelCommissionService;
use App\Services\PVService;
use App\Services\RankService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessRegistrationCommissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private User $user,
        private ?Order $order = null,
    ) {
        $this->onQueue('commissions');
    }

    public function handle(
        PVService $pvService,
        LevelCommissionService $levelCommissionService,
        CommissionService $commissionService,
        RankService $rankService,
        BinaryTreeService $treeService,
    ): void {
        $lockKey = "commission:registration:{$this->user->id}";

        Cache::lock($lockKey, 120)->get(function () use (
            $pvService, $levelCommissionService, $commissionService, $rankService, $treeService
        ) {
            Log::info("Processing registration commissions for user #{$this->user->id}");

            $product = $this->user->product;
            if (!$product) return;

            // 1. Update Personal PV
            $pvService->updatePersonalPV($this->user, (float) $product->pv_value, 'registration', $product->id);

            // 2. Propagate Group PV to sponsor ancestors
            $pvService->propagateGroupPV($this->user, (float) $product->pv_value);

            // 3. Calculate Level Commission
            $levelCommissionService->calculate($this->user, $product, 'registration');

            // 4. Referral Commission (direct sponsor)
            $this->processReferralCommission($commissionService, $product);

            // 5. Check BlueDiamond auto-promote
            $treeService->checkAutoRankPromotion($this->user, (float) $product->pv_value);

            // 6. Check rank advancement
            $rankService->checkAndUpdateRank($this->user);

            // Also check sponsor's rank
            if ($this->user->sponsor_id) {
                $rankService->checkAndUpdateRank($this->user->sponsor);
            }

            Log::info("Completed registration commissions for user #{$this->user->id}");
        });
    }

    private function processReferralCommission(CommissionService $commissionService, $product): void
    {
        $sponsor = $this->user->sponsor;
        if (!$sponsor || !$sponsor->isActive()) return;

        $refAmount = (float) $product->referral_commission;
        if ($refAmount <= 0) return;

        $commissionService->creditCommission(
            user: $sponsor,
            amount: $refAmount,
            amountType: 'referral',
            fromUser: $this->user,
            product: $product,
            level: 1,
            note: "Referral commission from {$this->user->username}",
        );
    }
}
