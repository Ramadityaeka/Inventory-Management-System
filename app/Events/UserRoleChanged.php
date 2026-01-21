<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRoleChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public string $oldRole;
    public string $newRole;
    public User $admin;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $oldRole, string $newRole, User $admin)
    {
        $this->user = $user;
        $this->oldRole = $oldRole;
        $this->newRole = $newRole;
        $this->admin = $admin;
    }
}
