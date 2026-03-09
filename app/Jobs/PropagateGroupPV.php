<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\PVService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PropagateGroupPV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private User $user,
        private float $pvAmount,
    ) {
        $this->onQueue('commissions');
    }

    public function handle(PVService $pvService): void
    {
        $pvService->propagateGroupPV($this->user, $this->pvAmount);
    }
}
