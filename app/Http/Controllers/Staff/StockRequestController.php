<?php

namespace App\Http\Controllers\Staff;

use App\Events\StockRequestCreated;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockRequest;
use App\Models\Notification;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockRequestController extends Controller
{
    /**
     * Display available stock in warehouses
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $warehouses = $user->warehouses;
        
        // Get all stocks from user's warehouses with items (only active items)
        $query = Stock::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->with(['item.category', 'warehouse'])
            ->whereHas('item', function($q) {
                $q->where('is_active', true);
            });
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('category')) {
            $query->whereHas('item', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
        
        if ($request->filled('warehouse')) {
            $query->where('warehouse_id', $request->warehouse);
        }
        
        $stocks = $query->where('quantity', '>', 0)
            ->orderBy('warehouse_id')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        
        $categories = Category::orderBy('name')->get();
        
        return view('staff.stock-requests.index', compact('stocks', 'warehouses', 'categories'));
    }

    /**
     * Display list of my requests
     */
    public function myRequests(Request $request)
    {
        $query = StockRequest::where('staff_id', auth()->id())
            ->with(['item', 'warehouse', 'approver']);
        
        // Apply filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $requests = $query->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Get statistics
        $stats = [
            'pending' => StockRequest::where('staff_id', auth()->id())->pending()->count(),
            'approved' => StockRequest::where('staff_id', auth()->id())->approved()->count(),
            'rejected' => StockRequest::where('staff_id', auth()->id())->rejected()->count(),
        ];
        
        return view('staff.stock-requests.my-requests', compact('requests', 'stats'));
    }

    /**
     * Show form to create new stock request
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $warehouses = $user->warehouses;
        
        // Get all items with current stock in user's warehouses (only active items)
        $items = Stock::whereIn('warehouse_id', $warehouses->pluck('id'))
            ->where('quantity', '>', 0)
            ->with(['item.category', 'item.itemUnits', 'warehouse'])
            ->whereHas('item', function($q) {
                $q->where('is_active', true);
            })
            ->get()
            ->map(function($stock) {
                // Get available units for this item
                $availableUnits = $stock->item->itemUnits->map(function($unit) {
                    return [
                        'id' => $unit->id,
                        'name' => $unit->name,
                        'conversion_factor' => $unit->conversion_factor,
                    ];
                })->toArray();
                
                // Jika tidak ada itemUnits (belum di-set satuan konversi), 
                // gunakan satuan default dari item dengan conversion_factor = 1
                if (empty($availableUnits)) {
                    $availableUnits = [[
                        'id' => 0, // ID 0 untuk satuan default
                        'name' => $stock->item->unit,
                        'conversion_factor' => 1,
                    ]];
                }
                
                return [
                    'id' => $stock->item_id,
                    'name' => $stock->item->name,
                    'code' => $stock->item->code,
                    'unit' => $stock->item->unit,
                    'warehouse_id' => $stock->warehouse_id,
                    'warehouse_name' => $stock->warehouse->name,
                    'quantity' => $stock->quantity,
                    'available_units' => $availableUnits,
                ];
            });
        
        return view('staff.stock-requests.create', compact('warehouses', 'items'));
    }

    /**
     * Store new stock request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'unit_id' => 'required|integer',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            // Get item for base unit
            $item = \App\Models\Item::findOrFail($validated['item_id']);
            
            // Determine unit info based on unit_id
            $actualUnitId = $validated['unit_id'];
            if ($validated['unit_id'] == 0) {
                // Satuan default (belum di-set konversi)
                // Buat atau dapatkan ItemUnit default untuk item ini
                $defaultUnit = \App\Models\ItemUnit::firstOrCreate(
                    [
                        'item_id' => $item->id,
                        'name' => $item->unit,
                    ],
                    [
                        'conversion_factor' => 1,
                    ]
                );
                $actualUnitId = $defaultUnit->id;
                $unitName = $item->unit;
                $conversionFactor = 1;
            } else {
                // Get item unit to calculate base quantity
                $itemUnit = \App\Models\ItemUnit::findOrFail($validated['unit_id']);
                $unitName = $itemUnit->name;
                $conversionFactor = $itemUnit->conversion_factor;
            }
            
            // Calculate base quantity (quantity * conversion_factor)
            $baseQuantity = $validated['quantity'] * $conversionFactor;
            
            // Verify stock availability (in base unit)
            $stock = Stock::with('item')->where('item_id', $validated['item_id'])
                ->where('warehouse_id', $validated['warehouse_id'])
                ->lockForUpdate()
                ->first();
            
            if (!$stock || $stock->quantity < $baseQuantity) {
                $availableInRequestedUnit = $stock ? floor($stock->quantity / $conversionFactor) : 0;
                throw new \Exception("Stok tidak mencukupi. Stok tersedia: {$availableInRequestedUnit} {$unitName} (setara {$stock->quantity} {$item->unit})");
            }
            
            // Check if item is inactive
            if (!$stock->item->is_active) {
                $reason = $stock->item->inactive_reason;
                $reasonText = $reason === 'discontinued' ? 'tidak diproduksi lagi' : 
                             ($reason === 'wrong_input' ? 'salah input' : 'musiman (inactive)');
                throw new \Exception("Tidak dapat membuat permintaan. Barang {$stock->item->name} sudah dinonaktifkan ({$reasonText}).");
            }
            
            // Check if staff has access to this warehouse
            if (!auth()->user()->warehouses->contains($validated['warehouse_id'])) {
                throw new \Exception('Anda tidak memiliki akses ke gudang ini.');
            }
            
            // Create stock request with unit info
            $stockRequest = StockRequest::create([
                'item_id' => $validated['item_id'],
                'unit_id' => $actualUnitId,
                'warehouse_id' => $validated['warehouse_id'],
                'staff_id' => auth()->id(),
                'quantity' => $validated['quantity'],
                'unit_name' => $unitName,
                'conversion_factor' => $conversionFactor,
                'base_quantity' => $baseQuantity,
                'purpose' => $validated['purpose'],
                'notes' => $validated['notes'] ?? null,
                'status' => StockRequest::STATUS_PENDING,
            ]);
            
            // Dispatch event untuk notifikasi admin gudang
            event(new StockRequestCreated($stockRequest));
        });

        return redirect()->route('staff.stock-requests.my-requests')
            ->with('success', 'Request penggunaan barang berhasil diajukan dan menunggu persetujuan admin.');
    }

    /**
     * Display specific request
     */
    public function show(StockRequest $stockRequest)
    {
        // Authorization check
        if ($stockRequest->staff_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        $stockRequest->load(['item.category', 'warehouse', 'staff', 'approver']);
        
        return view('staff.stock-requests.show', compact('stockRequest'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockRequest $stockRequest)
    {
        // Authorization check
        if ($stockRequest->staff_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        
        // Can only cancel pending requests
        if ($stockRequest->status !== StockRequest::STATUS_PENDING) {
            return redirect()->back()
                ->with('error', 'Hanya request dengan status pending yang dapat dibatalkan.');
        }
        
        $stockRequest->delete();
        
        return redirect()->route('staff.stock-requests.my-requests')
            ->with('success', 'Request penggunaan barang berhasil dibatalkan.');
    }
}
