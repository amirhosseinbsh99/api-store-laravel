<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BasketController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);


// Protected routes example
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard']);
    Route::put('/dashboard/update', [AuthController::class, 'updateDashboard']);
    Route::post('/dashboard/confirm-phone-change', [AuthController::class, 'confirmPhoneChange']);
    // Route::put('/users/{id}', [UserController::class, 'update']); // Admin updates any user
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/basket/add', [BasketController::class, 'addToBasket']);
    Route::get('/basket', [BasketController::class, 'viewBasket']);
    Route::delete('/basket/remove', [BasketController::class, 'removeFromBasket']);
    Route::put('/basket/update', [BasketController::class, 'updateBasket']);
    Route::post('basket/checkout', [PaymentController::class, 'checkout']);
    Route::get('basket/verify', [PaymentController::class, 'verify']);
});

Route::middleware('api')->group(function () {
    Route::get('/', [ProductController::class, 'home']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/categories', [CategoryController::class, 'index']);

});
Route::apiResource('products', ProductController::class);

