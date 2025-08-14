<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\BasketItem;
use App\Models\Product;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    public function addToBasket(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $basket = Basket::firstOrCreate(['user_id' => $request->user()->id]);

        $item = $basket->items()->where('product_id', $request->product_id)->first();

        if ($item) {
            $item->increment('quantity', $request->quantity);
        } else {
            $basket->items()->create([
                'product_id' => $request->product_id,
                'quantity'   => $request->quantity
            ]);
        }

        return response()->json(['message' => 'Product added to basket successfully']);
    }

    public function updateBasket(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        $basketItem = BasketItem::whereHas('basket', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })
        ->where('product_id', $request->product_id)
        ->first();

        if (!$basketItem) {
            return response()->json(['message' => 'Product not found in basket'], 404);
        }

        $basketItem->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'message' => 'Basket updated successfully',
            'basket'  => $basketItem
        ]);
    }

    public function viewBasket(Request $request)
    {
        $basket = Basket::where('user_id', $request->user()->id)
            ->with('items.product')
            ->first();

        if (!$basket) {
            return response()->json(['message' => 'Basket is empty']);
        }

        // Calculate total price
        $totalPrice = $basket->items->sum(function($item) {
        return $item->product->discounted_price * $item->quantity;
    });


        return response()->json([
            'basket' => $basket,
            'total_price' => $totalPrice, // total basket price
        ]);
    }

    public function removeFromBasket(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $basket = Basket::where('user_id', $request->user()->id)->firstOrFail();
        $basket->items()->where('product_id', $request->product_id)->delete();

        return response()->json(['message' => 'Product removed from basket']);
    }
}
