<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Jobs\ProcessRegistrationCommissions;
use App\Services\BinaryTreeService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleUserRegistered implements ShouldQueue
{
    public function __construct(
        private BinaryTreeService $treeService,
    ) {}

    public function handle(UserRegistered $event): void
    {
        // Add to binary tree + sponsor tree
        $this->treeService->addToTree($event->user);

        // Dispatch commission calculation job
        ProcessRegistrationCommissions::dispatch($event->user, $event->order);
    }
}
