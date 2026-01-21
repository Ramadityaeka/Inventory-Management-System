<?php

namespace App\Listeners;

use App\Events\UserRoleChanged;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserRoleChange
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
    public function handle(UserRoleChanged $event): void
    {
        $this->notificationService->notifyRoleChange(
            $event->user,
            $event->oldRole,
            $event->newRole,
            $event->admin
        );
    }
}
