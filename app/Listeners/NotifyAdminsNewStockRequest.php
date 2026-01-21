<?php

namespace App\Listeners;

use App\Events\StockRequestCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsNewStockRequest
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
    public function handle(StockRequestCreated $event): void
    {
        // Load necessary relationships
        $event->stockRequest->load(['item', 'staff', 'warehouse']);
        
        $this->notificationService->notifyAdminsNewStockRequest($event->stockRequest);
    }
}
