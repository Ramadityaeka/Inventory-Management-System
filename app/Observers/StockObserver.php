<?php

namespace App\Observers;

use App\Events\LowStockDetected;
use App\Models\Stock;

class StockObserver
{
    /**
     * Handle the Stock "updated" event.
     * Detect low stock dan dispatch event
     */
    public function updated(Stock $stock): void
    {
        // Check if quantity changed and is now low
        if ($stock->wasChanged('quantity')) {
            $stock->load('item');
            
            // Jika stock <= min_threshold dan quantity > 0, dispatch low stock event
            if ($stock->quantity > 0 && $stock->quantity <= $stock->item->min_threshold) {
                // Check if we haven't already notified recently (within last hour)
                // Use reference_type and reference_id to track specific item+warehouse combination
                $recentNotification = \App\Models\Notification::where('type', 'low_stock_alert')
                    ->where('reference_type', 'stock')
                    ->where('reference_id', $stock->id)
                    ->where('created_at', '>=', now()->subHour())
                    ->exists();
                
                if (!$recentNotification) {
                    event(new LowStockDetected($stock));
                }
            }
        }
    }
}
