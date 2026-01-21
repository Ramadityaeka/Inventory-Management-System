<?php

namespace App\Listeners;

use App\Events\UserPasswordReset;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserPasswordReset
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
    public function handle(UserPasswordReset $event): void
    {
        $this->notificationService->notifyPasswordReset(
            $event->user,
            $event->admin
        );
    }
}
