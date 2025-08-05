<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::middleware('api')->group(function () {
    Route::get('/', [ProductController::class, 'home']);
    Route::get('/products/search', [ProductController::class, 'search']);
    Route::get('/categories', [CategoryController::class, 'index']);

});
Route::apiResource('products', ProductController::class);

