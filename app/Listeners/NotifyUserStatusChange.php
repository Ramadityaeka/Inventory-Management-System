<?php

namespace App\Listeners;

use App\Events\UserStatusChanged;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserStatusChange
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
    public function handle(UserStatusChanged $event): void
    {
        $this->notificationService->notifyStatusChange(
            $event->user,
            $event->oldStatus,
            $event->newStatus,
            $event->admin
        );
    }
}
