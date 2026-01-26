<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\StockAlert;
use App\Models\Stock;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        // Get user warehouse IDs
        $userWarehouses = auth()->user()->warehouses()->pluck('warehouses.id');
        
        if ($userWarehouses->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to any warehouse.');
        }
        
        // Query for stock alerts
        $query = StockAlert::with(['item', 'warehouse'])
            ->whereIn('warehouse_id', $userWarehouses);
        
        // Filter by alert type
        if ($request->filled('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }
        
        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }
        
        // Get alerts ordered by created_at desc
        $alerts = $query->orderBy('is_read', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get alert statistics
        $statistics = [
            'total' => StockAlert::whereIn('warehouse_id', $userWarehouses)->count(),
            'unread' => StockAlert::whereIn('warehouse_id', $userWarehouses)->where('is_read', false)->count(),
            'low_stock' => StockAlert::whereIn('warehouse_id', $userWarehouses)
                ->where('alert_type', StockAlert::ALERT_TYPE_LOW_STOCK)->count(),
            'out_of_stock' => StockAlert::whereIn('warehouse_id', $userWarehouses)
                ->where('alert_type', StockAlert::ALERT_TYPE_OUT_OF_STOCK)->count(),
        ];
        
        return view('gudang.alerts.index', compact('alerts', 'statistics'));
    }
    
    public function markAsRead(Request $request, $alertId)
    {
        $alert = StockAlert::findOrFail($alertId);
        
        // Check if user has access to this warehouse
        if (!auth()->user()->warehouses->contains($alert->warehouse_id)) {
            return redirect()->back()->with('error', 'You do not have access to this alert.');
        }
        
        $alert->update(['is_read' => true]);
        
        return redirect()->back()->with('success', 'Alert marked as read.');
    }
}