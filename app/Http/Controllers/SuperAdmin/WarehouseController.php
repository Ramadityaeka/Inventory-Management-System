<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('location', 'LIKE', "%{$search}%");
            });
        }

        $warehouses = $query->orderBy('code', 'asc')->paginate(20);

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $nextCode = $this->generateNextCode();
        return view('admin.warehouses.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:10|unique:warehouses',
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = $this->generateNextCode();
        }

        // Set default is_active to true if not provided
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Warehouse::create($validated);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    public function show(Request $request, Warehouse $warehouse)
    {
        try {
            // Ensure warehouse exists and load relations
            $warehouse->load(['stocks.item.category', 'stocks.item.itemUnits']);
            
            // Process stocks with defensive programming
            $processedStocks = collect();
            
            foreach ($warehouse->stocks as $stock) {
                // Skip if item doesn't exist
                if (!$stock->item) {
                    continue;
                }

                // Get base unit safely
                $baseUnit = 'PCS';
                try {
                    if ($stock->item->itemUnits && count($stock->item->itemUnits) > 0) {
                        $baseUnitModel = $stock->item->itemUnits->firstWhere('is_base_unit', true);
                        if ($baseUnitModel && isset($baseUnitModel->unit_name)) {
                            $baseUnit = $baseUnitModel->unit_name;
                        }
                    }
                } catch (\Exception $e) {
                    // Keep default PCS if error
                }

                $quantity = $stock->quantity ?? 0;
                $minStock = $stock->min_stock ?? 0;
                
                $processedStocks->push([
                    'id' => $stock->id ?? 0,
                    'item_id' => $stock->item_id ?? 0,
                    'item_name' => $stock->item->name ?? 'N/A',
                    'item_code' => $stock->item->code ?? 'N/A',
                    'category_name' => optional($stock->item->category)->name ?? 'Tanpa Kategori',
                    'quantity' => $quantity,
                    'min_stock' => $minStock,
                    'max_stock' => $stock->max_stock ?? 0,
                    'status' => $quantity <= 0 ? 'out_of_stock' : ($quantity <= $minStock ? 'low_stock' : 'normal'),
                    'base_unit' => $baseUnit,
                    'last_updated' => $stock->updated_at ?? now(),
                ]);
            }
            
            // Sort by item name
            $allStocks = $processedStocks->sortBy('item_name')->values();

            // Apply server-side search filter
            if ($request->filled('stock_search')) {
                $search = strtolower($request->stock_search);
                $allStocks = $allStocks->filter(function ($stock) use ($search) {
                    return str_contains(strtolower($stock['item_name']), $search)
                        || str_contains(strtolower($stock['item_code']), $search)
                        || str_contains(strtolower($stock['category_name']), $search);
                })->values();
            }

            // Calculate statistics from FULL collection (before pagination, after search)
            $stats = [
                'total_items' => $processedStocks->count(),
                'total_stock' => $processedStocks->sum('quantity'),
                'out_of_stock' => $processedStocks->where('status', 'out_of_stock')->count(),
                'low_stock' => $processedStocks->where('status', 'low_stock')->count(),
                'normal_stock' => $processedStocks->where('status', 'normal')->count(),
            ];

            // Server-side pagination for stocks
            $perPage = 25;
            $currentPage = $request->input('stocks_page', 1);
            $stocks = new LengthAwarePaginator(
                $allStocks->forPage($currentPage, $perPage),
                $allStocks->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'pageName' => 'stocks_page', 'query' => $request->query()]
            );

            // Get stock movements with server-side pagination
            try {
                $recentMovements = DB::table('stock_movements')
                    ->join('items', 'stock_movements.item_id', '=', 'items.id')
                    ->leftJoin('users', 'stock_movements.created_by', '=', 'users.id')
                    ->where(function($query) use ($warehouse) {
                        $query->where('stock_movements.warehouse_id', $warehouse->id)
                              ->orWhere('stock_movements.unit_id', $warehouse->id);
                    })
                    ->select(
                        'stock_movements.*',
                        DB::raw('stock_movements.movement_type as type'),
                        'items.name as item_name',
                        'items.code as item_code',
                        DB::raw('COALESCE(users.name, "System") as user_name')
                    )
                    ->orderBy('stock_movements.created_at', 'desc')
                    ->paginate(15, ['*'], 'movements_page');
            } catch (\Exception $e) {
                \Log::warning('Error fetching stock movements: ' . $e->getMessage());
                $recentMovements = new LengthAwarePaginator(collect(), 0, 15);
            }

            return view('admin.warehouses.show', compact('warehouse', 'stocks', 'stats', 'recentMovements'));
            
        } catch (\Exception $e) {
            \Log::error('Error in WarehouseController@show: ' . $e->getMessage(), [
                'warehouse_id' => $warehouse->id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Terjadi kesalahan saat memuat detail unit. Silakan coba lagi.');
        }
    }

    public function edit(Warehouse $warehouse)
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('warehouses')->ignore($warehouse->id)],
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'address' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle is_active checkbox
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        $warehouse->update($validated);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse)
    {
        // Check if warehouse has stocks
        if ($warehouse->stocks()->exists()) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Cannot delete warehouse that contains items.');
        }

        // Soft delete by setting is_active to false
        $warehouse->update(['is_active' => false]);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Warehouse deactivated successfully.');
    }

    /**
     * Generate the next warehouse code (GD-001, GD-002, etc.)
     */
    private function generateNextCode(): string
    {
        // Get the highest number from existing codes
        $lastCode = Warehouse::where('code', 'LIKE', 'GD-%')
            ->orderByRaw("CAST(SUBSTRING(code, 4) AS UNSIGNED) DESC")
            ->value('code');

        if ($lastCode) {
            // Extract number from GD-XXX format
            $number = (int) substr($lastCode, 3);
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }

        return 'GD-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}