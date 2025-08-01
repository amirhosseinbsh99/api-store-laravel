<?php
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});
// Example public API route
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
