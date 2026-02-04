<?php

namespace App\Listeners;

use App\Events\SubmissionCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsNewSubmission
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
    public function handle(SubmissionCreated $event): void
    {
        // Log untuk debugging duplikasi
        \Log::info('NotifyAdminsNewSubmission triggered for submission: ' . $event->submission->id);
        
        // Load necessary relationships
        $event->submission->load(['item', 'staff', 'warehouse']);
        
        $this->notificationService->notifyAdminsNewSubmission($event->submission);
    }
}
