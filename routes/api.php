<?php

use App\Category;
use App\Product;
use Illuminate\Http\Request;
use Tortuga\ApiTransformer\GetCategoriesApiTransformer;
use Tortuga\ApiTransformer\GetProductsApiTransformer;

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

Route::middleware('cors')->get('/categories', function (Request $request) {
    $categories  = Category::has('products')->ordered()->get()->toArray();
    $transformer = new GetCategoriesApiTransformer();

    return response()->json($transformer->output($categories));
});

Route::middleware('cors')->get('/products', function (Request $request) {
    $products    = Product::with('variations')->ordered()->get()->toArray();
    $transformer = new GetProductsApiTransformer();

    return response()->json($transformer->output($products));
});

Route::fallback(function () {
    return response()->json(['errors' => ['Not Found.']], 404);
})->name('api.fallback.404')->middleware('cors');