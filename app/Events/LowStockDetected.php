<?php

namespace App\Events;

use App\Models\Stock;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Stock $stock;

    /**
     * Create a new event instance.
     */
    public function __construct(Stock $stock)
    {
        $this->stock = $stock;
    }
}
