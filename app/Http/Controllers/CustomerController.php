<?php

namespace App\Http\Controllers;

use App\Customer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tortuga\ApiTransformer\GetCustomersApiTransformer;
use Tortuga\Validation\AccountKitException;
use Tortuga\Validation\InvalidDataException;
use Tortuga\ApiTransformer\GetCustomerApiTransformer;
use Tortuga\Customer\CustomerRegistrationStrategy;

class CustomerController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $accountId = $request->get('account_id');
        $name      = $request->get('name');
        $regType   = $request->get('reg_type');

        if (!$accountId || !$name || !$regType) {
            return $this->_returnError();
        }

        try {
            /** @var Customer $customer */
            $customer = Customer::where('account_kit_id', $accountId)
                ->where('name', $name)
                ->where('reg_type', $regType)
                ->firstOrFail();

            $transformer = new GetCustomersApiTransformer();

            return response()->json($transformer->output([$customer->toArray()]));
        } catch (ModelNotFoundException $e) {
            return $this->_returnError();
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            /** @var CustomerRegistrationStrategy $strategy */
            $strategy = app()->make(CustomerRegistrationStrategy::class);

            $customer    = $strategy->registerCustomer(json_decode($request->getContent()));
            $transformer = new GetCustomerApiTransformer();

            return response()->json($transformer->output($customer->toArray()));
        } catch (InvalidDataException $e) {
            return $this->_returnError(422, 'JSON Schema Validation error', $e->getMessage(), $e->getDataPointer());
        } catch (AccountKitException $e) {
            return $this->_returnError(401, $e->getMessage(), $e->getMessage(), '/data/attributes/code');
        }
    }
}
