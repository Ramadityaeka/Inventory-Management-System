<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('parent');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Load items count (only active items)
        $categories = $query->withCount(['items' => function($q) {
            $q->where('is_active', 1);
        }])
        ->withCount('children')
        ->addSelect([
            'total_stock' => Stock::selectRaw('COALESCE(SUM(stocks.quantity), 0)')
                ->join('items', 'items.id', '=', 'stocks.item_id')
                ->whereColumn('items.category_id', 'categories.id'),
        ])
        ->orderBy('code', 'asc')
        ->paginate(50);

        // All categories for the quick-add modal dropdown
        $allCategories = Category::active()->orderBy('code', 'asc')->get();

        return view('admin.categories.index', compact('categories', 'allCategories'));
    }

    public function create(Request $request)
    {
        // Get all categories for parent selection
        $categories = Category::active()
            ->orderBy('code', 'asc')
            ->get();

        $parentId = $request->get('parent_id');
        
        return view('admin.categories.create', compact('categories', 'parentId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'code' => 'required|string|max:50|unique:categories',
            'description' => 'nullable|string',
        ]);

        // Validate last segment does not exceed 999
        $codeParts = explode('.', $validated['code']);
        $lastSegment = (int) end($codeParts);
        if (!empty($validated['parent_id']) && $lastSegment > 999) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Nomor urut kode "' . $validated['code'] . '" tidak boleh melebihi 999. Silakan gunakan kode lain yang tersedia.')
                ->withInput();
        }

        // Set default is_active to true
        $validated['is_active'] = true;

        $category = Category::create($validated);

        // Return JSON if AJAX request
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'code' => $category->code,
                    'name' => $category->name,
                ]
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category)
    {
        // Not needed for this implementation
    }

    public function edit(Category $category)
    {
        // Calculate total stock for this category
        $totalStock = \App\Models\Stock::whereHas('item', function($q) use ($category) {
            $q->where('category_id', $category->id);
        })->sum('quantity');
        
        $categories = Category::active()
            ->where('id', '!=', $category->id)
            ->orderBy('code', 'asc')
            ->get();
        
        return view('admin.categories.edit', compact('category', 'totalStock', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('categories')->ignore($category->id)],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        // Check if category has sub-categories
        if ($category->children()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang masih memiliki sub-kategori.');
        }

        // Check if any item in this category still has stock
        $totalStock = Stock::whereHas('item', function ($q) use ($category) {
            $q->where('category_id', $category->id);
        })->sum('quantity');

        if ($totalStock > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat menghapus kategori karena masih ada barang yang memiliki stock.');
        }

        // Orphan items (set category_id to null) then hard delete category
        $category->items()->update(['category_id' => null]);
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * API endpoint untuk pencarian kategori (AJAX)
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        
        $categories = Category::active()
            ->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            })
            ->orderBy('code', 'asc')
            ->limit(20)
            ->get()
            ->map(function($category) {
                return [
                    'id' => $category->id,
                    'code' => $category->code,
                    'name' => $category->name,
                    'full_name' => $category->code . ' - ' . $category->name,
                    'level' => $category->level,
                ];
            });

        return response()->json($categories);
    }

    /**
     * Generate next sub-category code for given parent
     */
    public function generateCode(Request $request)
    {
        $parentId = $request->get('parent_id');
        
        if (!$parentId) {
            return response()->json(['error' => 'Parent ID required'], 400);
        }

        $parent = Category::findOrFail($parentId);
        $nextCode = $parent->generateNextSubCategoryCode();

        // Check if last segment exceeds 999
        $parts = explode('.', $nextCode);
        $lastSegment = (int) end($parts);
        $overflow = $lastSegment > 999;

        return response()->json([
            'code' => $nextCode,
            'parent_code' => $parent->code,
            'parent_name' => $parent->name,
            'overflow' => $overflow,
        ]);
    }

    /**
     * Check if a category code is already taken (AJAX)
     */
    public function checkCode(Request $request)
    {
        $code      = $request->get('code');
        $excludeId = $request->get('exclude_id'); // used when editing

        if (!$code) {
            return response()->json(['available' => true]);
        }

        $query = Category::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'code'      => $code,
        ]);
    }
}
