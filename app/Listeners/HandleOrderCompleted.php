<?php

namespace App\Listeners;

use App\Events\OrderCompleted;
use App\Jobs\ProcessOrderCommissions;

class HandleOrderCompleted
{
    public function handle(OrderCompleted $event): void
    {
        ProcessOrderCommissions::dispatch($event->order);
    }
}
