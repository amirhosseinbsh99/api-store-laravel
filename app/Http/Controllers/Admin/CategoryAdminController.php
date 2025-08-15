<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryAdminController extends Controller
{
    /**
     * List all categories with pagination
     */
    public function index()
    {
        $categories = Category::withCount('products')->paginate(10);
        return response()->json($categories);
    }

    /**
     * Create a new category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Check if category already exists
        if (Category::where('name', $request->name)->exists()) {
            return response()->json([
                'message' => 'Category with this name already exists.'
            ], 409); // 409 Conflict
        }

        $category = Category::create([
            'name' => $request->name
        ]);

        return response()->json($category, 201);
    }


    /**
     * Show a single category
     */
    public function show(Category $category)
    {
        $category->load('products');
        return response()->json($category);
    }

    /**
     * Update a category
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($data);

        return response()->json($category);
    }

    /**
     * Delete a category
     */
    public function destroy(Category $category)
    {
        // Optional: Prevent deletion if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with products.'
            ], 400);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
