<?php

namespace App\Http\Controllers\AdminUnit;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // Get user warehouse IDs (use first warehouse for admin)
        $userWarehouses = auth()->user()->warehouses()->pluck('warehouses.id');
        
        if ($userWarehouses->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to any warehouse.');
        }
        
        $warehouseId = $userWarehouses->first();
        
        // Base query for stocks with item and category relationships
        $query = Stock::with(['item.category', 'warehouse'])
            ->where('warehouse_id', $warehouseId)
            ->join('items', 'stocks.item_id', '=', 'items.id')
            ->select('stocks.*');
        
        // Apply category filter
        if ($request->filled('category_id')) {
            $query->where('items.category_id', $request->category_id);
        }
        
        // Apply stock status filter
        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'out') {
                $query->where('stocks.quantity', '=', 0);
            }
            // 'all' or default - no additional filter
        }
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('items.name', 'LIKE', "%{$search}%");
        }
        
        // Order by item name ascending
        $query->orderBy('items.name', 'asc');
        
        // Get paginated results
        $stocks = $query->paginate(50)->appends($request->query());
        
        // Load recent stock movements for each stock (last 3 movements)
        $stocks->getCollection()->transform(function ($stock) {
            $stock->recent_movements = StockMovement::with(['creator'])
                ->where('item_id', $stock->item_id)
                ->where('warehouse_id', $stock->warehouse_id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            return $stock;
        });
        
        // Calculate statistics for the warehouse
        $statsQuery = Stock::where('warehouse_id', $warehouseId);
        
        $statistics = [
            'total_items' => $statsQuery->count(),
            'total_stock' => $statsQuery->clone()->where('quantity', '>', 0)->sum('quantity'),
            'out_stock_count' => $statsQuery->clone()->where('quantity', '=', 0)->count()
        ];
        
        // Get categories for filter dropdown
        $categories = Category::orderBy('name')->get();
        
        return view('gudang.stocks.index', compact(
            'stocks',
            'statistics', 
            'categories',
            'request'
        ));
    }

    public function create()
    {
        // Get user warehouses
        $userWarehouses = auth()->user()->warehouses;
        
        if ($userWarehouses->isEmpty()) {
            return redirect()->route('gudang.stocks.index')->with('error', 'You do not have access to any warehouse.');
        }
        
        // Get active items that can have stock added
        $items = Item::where('is_active', true)->orderBy('name')->get();
        
        return view('gudang.stocks.create', compact('userWarehouses', 'items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Check if user has access to the warehouse
            if (!auth()->user()->warehouses->contains($validated['warehouse_id'])) {
                return redirect()->back()->with('error', 'You do not have access to this warehouse.');
            }

            // Check if item is active
            $item = Item::findOrFail($validated['item_id']);
            if (!$item->is_active) {
                return redirect()->back()->with('error', 'Cannot add stock for inactive item.');
            }

            // Get or create stock record
            $stock = Stock::firstOrNew([
                'item_id' => $validated['item_id'],
                'warehouse_id' => $validated['warehouse_id'],
            ]);

            $oldQuantity = $stock->quantity ?? 0;
            $stock->quantity += $validated['quantity'];
            $stock->last_updated = now();
            $stock->save();

            // Record stock movement
            StockMovement::create([
                'item_id' => $validated['item_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'movement_type' => StockMovement::MOVEMENT_TYPE_ADJUSTMENT,
                'quantity' => $validated['quantity'],
                'reference_type' => 'manual_addition',
                'reference_id' => auth()->id(),
                'notes' => $validated['notes'] . ' (Penambahan manual oleh ' . auth()->user()->name . ')',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('gudang.stocks.index')->with('success', 
                "Stock {$item->name} berhasil ditambahkan. Stock sebelumnya: {$oldQuantity}, Stock sekarang: {$stock->quantity}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add stock: ' . $e->getMessage());
        }
    }

    public function movement(Request $request)
    {
        // Get user warehouse IDs
        $userWarehouses = auth()->user()->warehouses()->pluck('warehouses.id');
        
        if ($userWarehouses->isEmpty()) {
            return redirect()->route('gudang.stocks.index')->with('error', 'You do not have access to any warehouse.');
        }
        
        // Get stock movements for user warehouses
        $query = StockMovement::with(['creator', 'warehouse', 'item.category', 'submission.supplier', 'submission.staff'])
            ->whereIn('warehouse_id', $userWarehouses);
        
        // Apply filters
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        
        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }
        
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->paginate(50);
        
        // Get filter options
        $warehouses = auth()->user()->warehouses;
        $items = Item::whereHas('stocks', function($q) use ($userWarehouses) {
            $q->whereIn('warehouse_id', $userWarehouses);
        })->orderBy('name')->get();
        
        $movementTypes = [
            StockMovement::MOVEMENT_TYPE_IN => 'Masuk',
            StockMovement::MOVEMENT_TYPE_OUT => 'Keluar',
            StockMovement::MOVEMENT_TYPE_ADJUSTMENT => 'Penyesuaian',
        ];
        
        return view('gudang.stocks.movement', compact(
            'movements', 
            'warehouses', 
            'items', 
            'movementTypes', 
            'request'
        ));
    }

    public function history(Item $item, Request $request)
    {
        // Load item relationships
        $item->load(['category', 'supplier']);
        
        // Get user warehouse IDs
        $userWarehouses = auth()->user()->warehouses()->pluck('warehouses.id');
        
        if ($userWarehouses->isEmpty()) {
            return redirect()->back()->with('error', 'You do not have access to any warehouse.');
        }
        
        // Check if user has access to at least one warehouse with this item
        $hasAccess = Stock::where('item_id', $item->id)
            ->whereIn('warehouse_id', $userWarehouses)
            ->exists();
            
        if (!$hasAccess) {
            return redirect()->back()->with('error', 'You do not have access to this item.');
        }
        
        // Get stock movements for this item
        $query = StockMovement::with(['creator', 'warehouse', 'item', 'submission.supplier', 'submission.staff'])
            ->where('item_id', $item->id)
            ->whereIn('warehouse_id', $userWarehouses);
        
        // Filter by warehouse if specified
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        // Filter by movement type
        if ($request->filled('type')) {
            $query->where('movement_type', $request->type);
        }
        
        $movements = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get current stocks for this item in user warehouses
        $currentStocks = Stock::with('warehouse')
            ->where('item_id', $item->id)
            ->whereIn('warehouse_id', $userWarehouses)
            ->get();
        
        // Get warehouses for filter
        $warehouses = auth()->user()->warehouses;
        
        return view('gudang.stocks.history', compact('item', 'movements', 'currentStocks', 'warehouses'));
    }
    
    public function adjustment(Request $request)
    {
        $validated = $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'adjustment_type' => 'required|in:add,reduce',
            'quantity' => 'required|integer|min:1',
            'notes' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Get stock
            $stock = Stock::with(['item', 'warehouse'])->findOrFail($validated['stock_id']);

            // Check if user has access to this warehouse
            if (!auth()->user()->warehouses->contains($stock->warehouse_id)) {
                return redirect()->back()->with('error', 'You do not have access to this warehouse.');
            }

            // Check if item is inactive
            if (!$stock->item->is_active) {
                $reason = $stock->item->inactive_reason;
                $reasonText = $reason === 'discontinued' ? 'tidak diproduksi lagi' : 
                             ($reason === 'wrong_input' ? 'salah input' : 'musiman (inactive)');
                return redirect()->back()->with('error', "Tidak dapat melakukan adjustment. Barang {$stock->item->name} sudah dinonaktifkan ({$reasonText}).");
            }

            // Calculate new quantity
            $quantity = $validated['quantity'];
            if ($validated['adjustment_type'] === 'reduce') {
                $quantity = -$quantity;
                
                // Check if stock is sufficient for reduction
                if ($stock->quantity + $quantity < 0) {
                    return redirect()->back()->with('error', 'Insufficient stock. Current stock: ' . $stock->quantity);
                }
            }

            // Update stock
            $oldQuantity = $stock->quantity;
            $stock->quantity += $quantity;
            $stock->last_updated = now();
            $stock->save();

            // Record stock movement
            StockMovement::create([
                'item_id' => $stock->item_id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_type' => StockMovement::MOVEMENT_TYPE_ADJUSTMENT,
                'quantity' => $quantity,
                'reference_type' => 'manual_adjustment',
                'reference_id' => auth()->id(),
                'notes' => $validated['notes'] . ' (' . ($validated['adjustment_type'] === 'add' ? 'Penambahan' : 'Pengurangan') . ' oleh ' . auth()->user()->name . ')',
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            $actionText = $validated['adjustment_type'] === 'add' ? 'ditambahkan' : 'dikurangi';
            return redirect()->route('gudang.stocks.index')->with('success', 
                "Stock {$stock->item->name} berhasil {$actionText}. Stock sebelumnya: {$oldQuantity}, Stock sekarang: {$stock->quantity}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }
}