<?php

namespace App\Events;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserWarehouseAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Warehouse $warehouse;
    public User $admin;
    public string $action; // 'assigned' or 'removed'

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Warehouse $warehouse, User $admin, string $action = 'assigned')
    {
        $this->user = $user;
        $this->warehouse = $warehouse;
        $this->admin = $admin;
        $this->action = $action;
    }
}
