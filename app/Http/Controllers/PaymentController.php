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
            return 'Basket is empty or invalid.';
        }

        // Calculate total considering quantity and discount
        $totalAmount = $basket->items->sum(function($item) {
            $price = $item->product->price;

            // Apply discount if exists
            if ($item->product->discount > 0) {
                $price = $price - ($price * $item->product->discount / 100);
            }

            return $price * $item->quantity;
        });

        if ($totalAmount <= 0) {
            return 'Basket is empty or invalid.';
        }

        $response = zarinpal()
            ->merchantId('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx')
            ->amount($totalAmount)
            ->request()
            ->description('transaction info')
            ->callbackUrl(route('payment.callback'))
            ->send();

        if (!$response->success()) {
            return $response->error()->message();
        }

        // Save transaction info to DB here

        return $response->redirect();
    }



    public function verify(Request $request)
    {
        $authority = request()->query('Authority'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال
        $status = request()->query('Status'); // دریافت کوئری استرینگ ارسال شده توسط زرین پال

        $response = zarinpal()
            ->merchantId('00000000-0000-0000-0000-000000000000') // تعیین مرچنت کد در حین اجرا - اختیاری
            ->amount(100)
            ->verification()
            ->authority($authority)
            ->send();

        if (!$response->success()) {
            return $response->error()->message();
        }

        // دریافت هش شماره کارتی که مشتری برای پرداخت استفاده کرده است
        // $response->cardHash();

        // دریافت شماره کارتی که مشتری برای پرداخت استفاده کرده است (بصورت ماسک شده)
        // $response->cardPan();

        // پرداخت موفقیت آمیز بود
        // دریافت شماره پیگیری تراکنش و انجام امور مربوط به دیتابیس
        return $response->referenceId();
    }
}


