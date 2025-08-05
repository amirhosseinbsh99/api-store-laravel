<?php
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});
