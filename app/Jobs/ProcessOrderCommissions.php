<?php

namespace App\Jobs;

use App\Models\Order;
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

class ProcessOrderCommissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private Order $order,
    ) {
        $this->onQueue('commissions');
    }

    public function handle(
        PVService $pvService,
        LevelCommissionService $levelCommissionService,
        CommissionService $commissionService,
        RankService $rankService,
    ): void {
        $user = $this->order->user;
        $lockKey = "commission:order:{$this->order->id}";

        Cache::lock($lockKey, 120)->get(function () use (
            $user, $pvService, $levelCommissionService, $commissionService, $rankService
        ) {
            Log::info("Processing order commissions for order #{$this->order->id}");

            $orderType = $this->order->type; // repurchase or upgrade

            // 1. Update Personal PV
            $pvService->updatePersonalPV($user, (float) $this->order->total_pv, $orderType);

            // 2. Propagate Group PV
            $pvService->propagateGroupPV($user, (float) $this->order->total_pv);

            // 3. Level Commission for each item
            foreach ($this->order->items as $item) {
                $levelCommissionService->calculate($user, $item->product, $orderType);
            }

            // 4. Check rank advancement
            $rankService->checkAndUpdateRank($user);

            Log::info("Completed order commissions for order #{$this->order->id}");
        });
    }
}
