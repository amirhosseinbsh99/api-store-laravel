<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Zarinpal;
use App\Models\BasketItem;

class PaymentController extends Controller
{
    

    public function checkout(Request $request)
    {
        $user = $request->user();

        // Get user's basket with items and products
        $basket = Basket::where('user_id', $user->id)
            ->with('items.product')
            ->first();

        if (!$basket || $basket->items->isEmpty()) {
            return response()->json(['message' => 'Basket is empty or invalid.'], 400);
        }

        // Calculate total considering quantity and discount
        $totalAmount = $basket->items->sum(function($item) {
            $price = $item->product->price;

            if ($item->product->discount > 0) {
                $price -= ($price * $item->product->discount / 100);
            }

            return $price * $item->quantity;
        });

        if ($totalAmount <= 0) {
            return response()->json(['message' => 'Basket is empty or invalid.'], 400);
        }

        // Send to Zarinpal
        $response = zarinpal()
            ->merchantId('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx') // replace your id
            ->amount($totalAmount)
            ->request()
            ->description('transaction info')
            ->callbackUrl(route('payment.callback')) // callback should hit verify()
            ->send();

        if (!$response->success()) {
            return $response->error()->message();
        }
        
        // Create Order with status 'pending' and save authority
        $order = Order::create([
            'user_id' => $user->id,
            'total' => $totalAmount,
            'status' => 'pending',
            'authority' => $response->authority(), // ✅ Store for verify()
        ]);
        
        // Save order items
        foreach ($basket->items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->price,
            ]);
        }

        return response()->json([
            'payment_url' => $response->url(),
            'total_amount' => $totalAmount,
            'authority' => $response->authority()
        ]);
    }


    public function verify(Request $request)
    {
        $authority = $request->query('Authority');
        $status = $request->query('Status');

        // Find the order based on authority
        $order = Order::where('authority', $authority)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Check if the order has expired
        if ($order->expires_at && now()->greaterThan($order->expires_at)) {
            $order->update(['status' => 'failed']);
            return response()->json(['message' => 'Payment window expired. Order failed.'], 400);
        }

        // Optional: If user canceled the payment
        if ($status !== 'OK') {
            $order->update(['status' => 'failed']);
            return response()->json(['message' => 'Payment was canceled.'], 400);
        }

        $totalAmount = $order->total;

        // Verify with Zarinpal
        $response = zarinpal()
            ->merchantId('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx') // replace with your merchant ID
            ->amount($totalAmount)
            ->verification()
            ->authority($authority)
            ->send();

        if (!$response->success()) {
            $order->update(['status' => 'failed']);
            return $response->error()->message();
        }

        // Update order as paid
        $order->update([
            'status' => 'paid',
            'reference_id' => $response->referenceId()
        ]);

        return response()->json([
            'message' => 'Payment successful',
            'reference_id' => $response->referenceId(),
            'total_amount' => $totalAmount
        ]);
    }


}


