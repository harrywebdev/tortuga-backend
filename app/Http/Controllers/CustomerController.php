<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tortuga\Api\AccountKitException;
use Tortuga\Api\InvalidDataException;
use Tortuga\ApiTransformer\GetCustomerApiTransformer;
use Tortuga\Customer\CustomerRegistrationStrategy;

class CustomerController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
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
    }
}
