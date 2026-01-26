<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with(['category', 'supplier', 'stocks']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Apply category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'discontinued') {
                $query->where('is_active', false)
                      ->where('inactive_reason', Item::INACTIVE_REASON_DISCONTINUED);
            } elseif ($status === 'wrong_input') {
                $query->where('is_active', false)
                      ->where('inactive_reason', Item::INACTIVE_REASON_WRONG_INPUT);
            } elseif ($status === 'seasonal') {
                $query->where('inactive_reason', Item::INACTIVE_REASON_SEASONAL);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $items = $query
            ->orderBy('name', 'asc')
            ->paginate(20)
            ->through(function ($item) {
                $item->total_stock = $item->stocks->sum('quantity');
                return $item;
            });

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.items.index', compact('items', 'categories', 'suppliers'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.items.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Auto-generate code based on category: PREFIX-YYYY-NNN
        $validated['code'] = generateItemCode($validated['category_id']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Item::create($validated);

        return redirect()->route('admin.items.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(Item $item)
    {
        $item->load([
            'category',
            'supplier',
            'stocks.warehouse',
            'deactivatedBy',
            'replacementItem',
            'stockMovements' => function ($query) {
                $query->with(['creator:id,name', 'warehouse:id,name'])
                      ->orderBy('created_at', 'desc')
                      ->limit(20);
            }
        ]);

        $totalStock = $item->stocks->sum('quantity');

        return view('admin.items.show', compact('item', 'totalStock'));
    }

    public function edit(Item $item)
    {
        $item->load(['category', 'supplier', 'deactivatedBy', 'replacementItem']);

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.items.edit', compact('item', 'categories', 'suppliers'));
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'inactive_reason' => 'nullable|in:discontinued,wrong_input,seasonal',
            'inactive_notes' => 'nullable|string',
            'replaced_by_item_id' => [
                'nullable',
                'exists:items,id',
                function ($attribute, $value, $fail) use ($item) {
                    if ($value && $value == $item->id) {
                        $fail('Item tidak bisa menggantikan dirinya sendiri.');
                    }
                },
            ],
        ]);

        return DB::transaction(function () use ($request, $item, $validated) {
            $wasActive = $item->is_active;
            $isActive = $request->has('is_active') ? $request->boolean('is_active') : false;
            
            // Prepare update data
            $updateData = [
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'supplier_id' => $validated['supplier_id'],
                'unit' => $validated['unit'],
                'description' => $validated['description'],
                'is_active' => $isActive,
            ];

            // Handle status change
            if ($wasActive && !$isActive) {
                // Deactivating
                $updateData['inactive_reason'] = $validated['inactive_reason'] ?? null;
                $updateData['inactive_notes'] = $validated['inactive_notes'] ?? null;
                $updateData['deactivated_at'] = now();
                $updateData['deactivated_by'] = auth()->id();
                $updateData['replaced_by_item_id'] = $validated['replaced_by_item_id'] ?? null;

                // Handle discontinued: clear stock
                if ($updateData['inactive_reason'] === Item::INACTIVE_REASON_DISCONTINUED) {
                    $this->clearStock($item, $updateData['inactive_notes'] ?? 'Barang tidak diproduksi lagi');
                }
            } elseif (!$wasActive && $isActive) {
                // Activating - clear deactivation data except for seasonal
                if ($item->inactive_reason !== Item::INACTIVE_REASON_SEASONAL) {
                    $updateData['inactive_reason'] = null;
                    $updateData['inactive_notes'] = null;
                    $updateData['deactivated_at'] = null;
                    $updateData['deactivated_by'] = null;
                    $updateData['replaced_by_item_id'] = null;
                }
            } elseif (!$isActive) {
                // Still inactive, update reason/notes if provided
                if (isset($validated['inactive_reason'])) {
                    $updateData['inactive_reason'] = $validated['inactive_reason'];
                }
                if (isset($validated['inactive_notes'])) {
                    $updateData['inactive_notes'] = $validated['inactive_notes'];
                }
                if (isset($validated['replaced_by_item_id'])) {
                    $updateData['replaced_by_item_id'] = $validated['replaced_by_item_id'];
                }
            }

            $item->update($updateData);

            return redirect()->route('admin.items.index')
                ->with('success', 'Item updated successfully.');
        });
    }

    public function destroy(Item $item)
    {
        // Check if item has stock
        $totalStock = $item->stocks()->sum('quantity');
        if ($totalStock > 0) {
            return redirect()->route('admin.items.index')
                ->with('error', 'Cannot delete item that has stock in warehouses.');
        }

        // Soft delete by setting is_active to false
        $item->update(['is_active' => false]);

        return redirect()->route('admin.items.index')
            ->with('success', 'Item deactivated successfully.');
    }

    public function toggleStatus(Request $request, Item $item)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
            'inactive_reason' => 'nullable|in:discontinued,wrong_input,seasonal',
            'inactive_notes' => 'nullable|string',
            'replaced_by_item_id' => 'nullable|exists:items,id',
            'transfer_stock' => 'nullable|boolean', // Opsi untuk transfer stock ke replacement
        ]);

        return DB::transaction(function () use ($request, $item, $validated) {
            $isActive = $validated['is_active'];
            $inactiveReason = $validated['inactive_reason'] ?? null;

            if (!$isActive) {
                // Deactivating item
                $item->update([
                    'is_active' => false,
                    'inactive_reason' => $inactiveReason,
                    'inactive_notes' => $validated['inactive_notes'] ?? null,
                    'deactivated_at' => now(),
                    'deactivated_by' => auth()->id(),
                    'replaced_by_item_id' => $validated['replaced_by_item_id'] ?? null,
                ]);

                // Handle discontinued: clear stock via adjustment
                if ($inactiveReason === Item::INACTIVE_REASON_DISCONTINUED) {
                    $this->clearStock($item, $validated['inactive_notes'] ?? 'Barang tidak diproduksi lagi');
                }

                // Handle wrong_input with transfer_stock option
                if ($inactiveReason === Item::INACTIVE_REASON_WRONG_INPUT && 
                    isset($validated['replaced_by_item_id']) && 
                    ($validated['transfer_stock'] ?? false)) {
                    $replacementItem = Item::find($validated['replaced_by_item_id']);
                    $this->transferStock($item, $replacementItem, $validated['inactive_notes'] ?? 'Transfer stock dari barang salah input');
                }

                $message = 'Item berhasil dinonaktifkan.';
                if ($inactiveReason === Item::INACTIVE_REASON_DISCONTINUED) {
                    $message .= ' Stok telah dikosongkan via adjustment.';
                } elseif ($inactiveReason === Item::INACTIVE_REASON_WRONG_INPUT) {
                    if ($validated['transfer_stock'] ?? false) {
                        $message .= ' Stok telah ditransfer ke barang pengganti.';
                    } else {
                        $message .= ' Stok tetap ada di sistem. Silakan transfer manual jika diperlukan.';
                    }
                }
            } else {
                // Activating item
                $updates = ['is_active' => true];
                
                // If reactivating seasonal item, keep the seasonal reason
                if ($item->inactive_reason !== Item::INACTIVE_REASON_SEASONAL) {
                    $updates['inactive_reason'] = null;
                    $updates['inactive_notes'] = null;
                    $updates['deactivated_at'] = null;
                    $updates['deactivated_by'] = null;
                }
                
                $item->update($updates);
                $message = 'Item berhasil diaktifkan.';
            }

            return redirect()->route('admin.items.index')
                ->with('success', $message);
        });
    }

    protected function clearStock(Item $item, string $notes)
    {
        // Get all stocks for this item
        $stocks = Stock::where('item_id', $item->id)
            ->where('quantity', '>', 0)
            ->get();

        foreach ($stocks as $stock) {
            // Create stock movement for adjustment
            StockMovement::create([
                'item_id' => $item->id,
                'warehouse_id' => $stock->warehouse_id,
                'movement_type' => StockMovement::MOVEMENT_TYPE_ADJUSTMENT,
                'quantity' => -$stock->quantity, // Negative to reduce stock
                'reference_type' => 'item_discontinued',
                'reference_id' => $item->id,
                'notes' => "Pengosongan stok otomatis: {$notes}",
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            // Clear the stock
            $stock->update([
                'quantity' => 0,
                'last_updated' => now(),
            ]);
        }
    }
}