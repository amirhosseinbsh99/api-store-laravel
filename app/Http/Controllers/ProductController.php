<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
    }
    public function home()
    {
        $shoesCategory = Category::where('name', 'Shoes')->first();

        // If no shoes category found, return message immediately
        if (!$shoesCategory) {
            return response()->json(['message' => 'No shoes category found'], 404);
        }

        $products = Product::where('category_id', $shoesCategory->id)
                    ->latest()
                    ->take(10)
                    ->get();

        // Check if products are empty
        if ($products->isEmpty()) {
            return response()->json(['message' => 'No shoes found'], 404);
        }

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
        ]);

        $product->update($validated);

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted']);

    }
    public function search(Request $request)
    {
        $query = $request->input('q');               // search keyword for name or description
        $categoryId = $request->input('category');  // category ID to filter by
        $minPrice = $request->input('min_price');   // minimum price
        $maxPrice = $request->input('max_price');   // maximum price

        $products = Product::query();

        // Filter by search keyword
        if ($query) {
            $products->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Filter by category ID
        if ($categoryId) {
            $products->where('category_id', $categoryId);
        }

        // Filter by price range
        if ($minPrice !== null) {
            $products->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $products->where('price', '<=', $maxPrice);
        }

        // Get results with category relationship
        $products = $products->with('category')->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found'], 404);
        }

        // Map to include category name, etc.
        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'category_name' => $product->category ? $product->category->name : null,
            ];
        });

        return response()->json($products);
    }


}
