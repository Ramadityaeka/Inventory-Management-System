<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
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