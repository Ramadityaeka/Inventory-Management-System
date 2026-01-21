<?php

namespace App\Events;

use App\Models\StockRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public StockRequest $stockRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(StockRequest $stockRequest)
    {
        $this->stockRequest = $stockRequest;
    }
}
