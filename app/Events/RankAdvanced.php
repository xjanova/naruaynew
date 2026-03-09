<?php

namespace App\Events;

use App\Models\Rank;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RankAdvanced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Rank $newRank,
        public ?int $oldRankId = null,
    ) {}
}
