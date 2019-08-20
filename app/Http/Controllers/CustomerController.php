<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Http\Resources\CustomerCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tortuga\Validation\AccountKitException;
use Tortuga\Validation\InvalidDataException;
use Tortuga\Customer\CustomerRegistrationStrategy;
use App\Http\Resources\Customer as CustomerResource;

class CustomerController extends Controller
{
    /**
     * CustomerController constructor.
     */
    public function __construct()
    {
        $this->middleware('throttle:20,1')->only('index');
    }

    /**
     * @param Request $request
     * @return CustomerCollection|\Illuminate\Http\JsonResponse
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

            return new CustomerCollection(new Collection([$customer]));
        } catch (ModelNotFoundException $e) {
            return $this->_returnError();
        }
    }

    /**
     * @param Request $request
     * @return CustomerResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            /** @var CustomerRegistrationStrategy $strategy */
            $strategy = app()->make(CustomerRegistrationStrategy::class);
            $customer = $strategy->registerCustomer(json_decode($request->getContent()));

            return new CustomerResource($customer);
        } catch (InvalidDataException $e) {
            return $this->_returnError(422, 'JSON Schema Validation error', $e->getMessage(), $e->getDataPointer());
        } catch (AccountKitException $e) {
            return $this->_returnError(401, $e->getMessage(), $e->getMessage(), '/data/attributes/code');
        }
    }
}
