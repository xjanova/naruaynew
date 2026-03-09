<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\RankService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckRankAdvancement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private User $user,
    ) {
        $this->onQueue('commissions');
    }

    public function handle(RankService $rankService): void
    {
        $rankService->checkAndUpdateRank($this->user);
    }
}
