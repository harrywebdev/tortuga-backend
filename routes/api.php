<?php

use App\Category;
use App\Product;
use Illuminate\Http\Request;
use Tortuga\Api\AccountKitException;
use Tortuga\Api\InvalidAttributeException;
use Tortuga\Api\InvalidResourceException;
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
    $strategy = new CustomerRegistrationStrategy();

    try {
        $resourceType = $request->input('data.type', '');
        if (!$resourceType || $resourceType !== 'customers') {
            throw new InvalidResourceException($resourceType);
        }

        $customer    = $strategy->registerCustomer(
            $request->input('data.attributes.reg_type', ''),
            $request->input('data.attributes', [])
        );
        $transformer = new GetCustomerApiTransformer();

        return response()->json($transformer->output($customer->toArray()));
    } catch (InvalidResourceException $e) {
        return response()->json((object)['errors' => [(object)[
            'status' => 422,
            'source' => (object)['pointer' => '/data/type'],
            'title'  => $e->getMessage(),
            'detail' => sprintf('Invalid resource type "%s" supplied instead of "%s"', $e->getResourceType(),
                'customers'),
        ],]], 400);
    } catch (InvalidAttributeException $e) {
        return response()->json((object)['errors' => [(object)[
            'status' => 422,
            'source' => (object)['pointer' => '/data/attributes/' . $e->getAttribute()],
            'title'  => $e->getMessage(),
            'detail' => $e->getDetail(),
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