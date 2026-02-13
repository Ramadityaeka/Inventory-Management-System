<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\StockRequest;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockRequestManagementController extends Controller
{
    /**
     * Display list of stock requests
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouseIds = $user->warehouses->pluck('id');
        
        $query = StockRequest::whereIn('warehouse_id', $warehouseIds)
            ->with(['item', 'warehouse', 'staff', 'approver']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $requests = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $stats = [
            'pending' => StockRequest::whereIn('warehouse_id', $warehouseIds)->pending()->count(),
            'approved' => StockRequest::whereIn('warehouse_id', $warehouseIds)->approved()->count(),
            'rejected' => StockRequest::whereIn('warehouse_id', $warehouseIds)->rejected()->count(),
        ];
        
        return view('gudang.stock-requests.index', compact('requests', 'stats'));
    }

    /**
     * Display specific request
     */
    public function show(StockRequest $stockRequest)
    {
        // Check if admin has access to this warehouse
        if (!auth()->user()->warehouses->contains($stockRequest->warehouse_id)) {
            abort(403, 'Unauthorized access to this warehouse.');
        }
        
        $stockRequest->load(['item.category', 'warehouse', 'staff', 'approver']);
        
        // Get current stock
        $currentStock = Stock::where('item_id', $stockRequest->item_id)
            ->where('warehouse_id', $stockRequest->warehouse_id)
            ->first();
        
        return view('gudang.stock-requests.show', compact('stockRequest', 'currentStock'));
    }

    /**
     * Approve stock request
     */
    public function approve(StockRequest $stockRequest)
    {
        // Check if admin has access to this warehouse
        if (!auth()->user()->warehouses->contains($stockRequest->warehouse_id)) {
            abort(403, 'Unauthorized access to this warehouse.');
        }
        
        // Check if still pending
        if ($stockRequest->status !== StockRequest::STATUS_PENDING) {
            return redirect()->back()
                ->with('error', 'Request ini sudah diproses sebelumnya.');
        }
        
        DB::transaction(function () use ($stockRequest) {
            // Verify stock availability
            $stock = Stock::where('item_id', $stockRequest->item_id)
                ->where('warehouse_id', $stockRequest->warehouse_id)
                ->lockForUpdate()
                ->first();
            
            if (!$stock || $stock->quantity < $stockRequest->base_quantity) {
                throw new \Exception('Stok tidak mencukupi. Stok saat ini: ' . ($stock ? $stock->quantity : 0) . ' (dibutuhkan: ' . $stockRequest->base_quantity . ')');
            }
            
            // Reduce stock
            $stock->decrement('quantity', $stockRequest->base_quantity);
            
            // Create stock movement
            StockMovement::create([
                'item_id' => $stockRequest->item_id,
                'unit_id' => $stockRequest->unit_id,
                'warehouse_id' => $stockRequest->warehouse_id,
                'movement_type' => 'out',
                'quantity' => -$stockRequest->base_quantity,  // Negative for stock out (in base unit)
                'reference_type' => 'stock_request',
                'reference_id' => $stockRequest->id,
                'notes' => 'Penggunaan barang approved - Request #' . $stockRequest->id . ' oleh ' . $stockRequest->staff->name,
                'created_by' => auth()->id(),
            ]);
            
            // Update request status
            $stockRequest->update([
                'status' => StockRequest::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            
            // Notify staff
            Notification::create([
                'user_id' => $stockRequest->staff_id,
                'title' => 'Request Penggunaan Barang Disetujui',
                'message' => 'Request penggunaan ' . $stockRequest->item->name . ' sebanyak ' . $stockRequest->quantity . ' telah disetujui oleh ' . auth()->user()->name . '.',
                'type' => 'info',
            ]);
        });
        
        return redirect()->back()
            ->with('success', 'Request berhasil disetujui dan stok telah dikurangi.');
    }

    /**
     * Reject stock request
     */
    public function reject(Request $request, StockRequest $stockRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        // Check if admin has access to this warehouse
        if (!auth()->user()->warehouses->contains($stockRequest->warehouse_id)) {
            abort(403, 'Unauthorized access to this warehouse.');
        }
        
        // Check if still pending
        if ($stockRequest->status !== StockRequest::STATUS_PENDING) {
            return redirect()->back()
                ->with('error', 'Request ini sudah diproses sebelumnya.');
        }
        
        DB::transaction(function () use ($stockRequest, $validated) {
            // Update request status
            $stockRequest->update([
                'status' => StockRequest::STATUS_REJECTED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);
            
            // Notify staff
            Notification::create([
                'user_id' => $stockRequest->staff_id,
                'title' => 'Request Penggunaan Barang Ditolak',
                'message' => 'Request penggunaan ' . $stockRequest->item->name . ' sebanyak ' . $stockRequest->quantity . ' telah ditolak. Alasan: ' . $validated['rejection_reason'],
                'type' => 'warning',
            ]);
        });
        
        return redirect()->back()
            ->with('success', 'Request berhasil ditolak.');
    }
}
