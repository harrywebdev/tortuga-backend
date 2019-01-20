<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/categories', function (Request $request) {
    /** @var \Illuminate\Database\Eloquent\Collection $products */
    $products = \App\Category::has('products')->ordered()->get();

    return response()->json($products->toArray());
});

Route::get('/products', function (Request $request) {
    /** @var \Illuminate\Database\Eloquent\Collection $products */
    $products = \App\Product::with('variations')->ordered()->get();

    return response()->json($products->toArray());
});
