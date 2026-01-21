<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public bool $oldStatus;
    public bool $newStatus;
    public User $admin;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, bool $oldStatus, bool $newStatus, User $admin)
    {
        $this->user = $user;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->admin = $admin;
    }
}
