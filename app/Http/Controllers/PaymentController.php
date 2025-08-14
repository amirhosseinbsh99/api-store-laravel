<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Zarinpal;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $response = zarinpal()
        ->merchantId('00000000-0000-0000-0000-000000000000') // تعیین مرچنت کد در حین اجرا - اختیاری
        ->amount(100) // مبلغ تراکنش
        ->request()
        ->description('transaction info') // توضیحات تراکنش
        ->callbackUrl('https://domain.com/verification') // آدرس برگشت پس از پرداخت
        ->mobile('09123456789') // شماره موبایل مشتری - اختیاری
        ->email('name@domain.com') // ایمیل مشتری - اختیاری
        ->send();

        if (!$response->success()) {
            return $response->error()->message();
        }

        // ذخیره اطلاعات تراکنش در دیتابیس

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


