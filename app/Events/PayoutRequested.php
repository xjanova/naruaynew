<?php

namespace App\Events;

use App\Models\PayoutRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PayoutRequest $payoutRequest,
    ) {}
}
