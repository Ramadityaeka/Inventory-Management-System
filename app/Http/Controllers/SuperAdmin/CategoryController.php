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
        $query = Category::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Load items count (only active items)
        $categories = $query->withCount(['items' => function($q) {
            $q->where('is_active', 1);
        }])
        ->orderBy('name', 'asc')
        ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'code_prefix' => 'required|string|max:5|unique:categories|regex:/^[A-Z]{3,5}$/',
            'description' => 'nullable|string',
        ]);

        // Set default is_active to true
        $validated['is_active'] = true;
        $validated['code_prefix'] = strtoupper($validated['code_prefix']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
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
        
        return view('admin.categories.edit', compact('category', 'totalStock'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'code_prefix' => ['required', 'string', 'max:5', 'regex:/^[A-Z]{3,5}$/', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
        ]);

        $validated['code_prefix'] = strtoupper($validated['code_prefix']);

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category has items
        if ($category->items()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category that contains items.');
        }

        // Soft delete by setting is_active to false
        $category->update(['is_active' => false]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deactivated successfully.');
    }
}
