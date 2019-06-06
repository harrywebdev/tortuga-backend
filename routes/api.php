<?php

use App\Category;
use App\Product;
use Illuminate\Http\Request;
use Tortuga\Api\AccountKitException;
use Tortuga\Api\InvalidDataException;
use Tortuga\ApiTransformer\GetCategoriesApiTransformer;
use Tortuga\ApiTransformer\GetCustomerApiTransformer;
use Tortuga\ApiTransformer\GetProductsApiTransformer;
use Tortuga\Customer\CustomerRegistrationStrategy;

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
    $categories  = Category::has('products')->ordered()->get()->toArray();
    $transformer = new GetCategoriesApiTransformer();

    return response()->json($transformer->output($categories));
});

Route::get('/products', function (Request $request) {
    $products    = Product::with('variations')->ordered()->get()->toArray();
    $transformer = new GetProductsApiTransformer();

    return response()->json($transformer->output($products));
});

Route::post('/customers', function (Request $request) {
    try {
        /** @var CustomerRegistrationStrategy $strategy */
        $strategy = app()->make(CustomerRegistrationStrategy::class);

        $customer    = $strategy->registerCustomer(json_decode($request->getContent()));
        $transformer = new GetCustomerApiTransformer();

        return response()->json($transformer->output($customer->toArray()));
    } catch (InvalidDataException $e) {
        return response()->json((object)['errors' => [(object)[
            'status' => 422,
            'source' => (object)['pointer' => $e->getDataPointer()],
            'title'  => 'JSON Schema Validation error',
            'detail' => $e->getMessage(),
        ],]], 400);
    } catch (AccountKitException $e) {
        return response()->json((object)['errors' => [(object)[
            'status' => 401,
            'source' => (object)['pointer' => '/data/attributes/code'],
            'title'  => $e->getMessage(),
            'detail' => $e->getMessage(),
        ],]], 400);
    } catch (\Exception $e) {
        return response()->json((object)['errors' => [(object)[
            'status' => 500,
            'title'  => 'Internal Server Error',
            'detail' => $e->getMessage(),
        ],]], 500);
    }
});

Route::fallback(function () {
    return response()->json(['errors' => ['Not Found.']], 404);
})->name('api.fallback.404');