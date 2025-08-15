<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    // List all orders with pagination
    public function index()
    {
        return Order::with(['user', 'items.product'])->paginate(10);
    }

    // Show a single order with its items
    public function show(Order $order)
    {
        return $order->load(['user', 'items.product']);
    }

    // Update an order (e.g., status)
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'sometimes|string|in:processing,completed,canceled',
        ]);

        $order->update($data);

        return response()->json($order);
    }

    // Delete an order
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
