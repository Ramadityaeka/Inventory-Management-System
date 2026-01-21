<?php

namespace App\Listeners;

use App\Events\UserWarehouseAssigned;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserWarehouseAssignment
{
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(UserWarehouseAssigned $event): void
    {
        // Get warehouse details
        $warehouses = [[
            'id' => $event->warehouse->id,
            'name' => $event->warehouse->name,
        ]];

        $this->notificationService->notifyWarehouseAssignment(
            $event->user,
            $warehouses,
            $event->admin,
            $event->action
        );
    }
}
