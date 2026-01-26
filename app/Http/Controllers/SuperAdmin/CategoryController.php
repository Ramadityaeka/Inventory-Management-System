<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        ->orderBy('code', 'asc')
        ->paginate(50);

        return view('admin.categories.index', compact('categories'));
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
            'code' => 'nullable|string|max:50|unique:categories',
            'description' => 'nullable|string',
        ]);

        // Auto-generate code if parent is selected and code not provided
        if (!empty($validated['parent_id']) && empty($validated['code'])) {
            $parent = Category::findOrFail($validated['parent_id']);
            $validated['code'] = $parent->generateNextSubCategoryCode();
        } elseif (empty($validated['code'])) {
            // Root category, must provide code manually
            return back()->withErrors(['code' => 'Kode harus diisi untuk kategori utama'])->withInput();
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
        // Check if category has items
        if ($category->items()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang masih memiliki barang.');
        }

        // Check if category has children
        if ($category->children()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Tidak dapat menghapus kategori yang masih memiliki sub-kategori.');
        }

        // Soft delete by setting is_active to false
        $category->update(['is_active' => false]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dinonaktifkan.');
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

        return response()->json([
            'code' => $nextCode,
            'parent_code' => $parent->code,
            'parent_name' => $parent->name,
        ]);
    }
}
