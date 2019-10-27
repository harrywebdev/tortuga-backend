<?php

use App\Category;
use App\Product;
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
    $categories = Category::has('products')->ordered()->get();

    return new \App\Http\Resources\CategoryCollection($categories);
});

Route::get('/products', function (Request $request) {
    // only variations that are active
    $products = Product::with(['variations' => function ($query) {
        $query->where('active', '=', 1);
    }])
        ->ordered()
        // only products with at least 1 active variation
        ->whereHas('variations', function (Illuminate\Database\Eloquent\Builder $query) {
            $query->where('active', '=', 1);
        })->get();
    
    return new \App\Http\Resources\ProductCollection($products);
});

Route::get('/slots', function (Request $request) {
    /** @var \Tortuga\SlotStrategy $strategy */
    $strategy = app()->make(\Tortuga\SlotStrategy::class);
    $slots    = $strategy->getAvailableSlots();

    return new \App\Http\Resources\SlotCollection($slots);
});

Route::resource('/customers', 'CustomerController')->only([
    'index', 'store',
]);

Route::resource('/orders', 'OrderController')->only([
    'index', 'store', 'update',
]);

Route::resource('/settings', 'SettingsController')->only([
    'update', 'show',
]);

Route::post('/replies', function (Request $request) {
    $data = json_decode($request->getContent(), true);

    if (count($data)) {
        \App\Jobs\ProcessCustomerReplies::dispatch($data);
    }

    return response()->json(['success' => true]);
});

Route::fallback(function () {
    return response()->json(['errors' => ['Not Found.']], 404);
})->name('api.fallback.404');