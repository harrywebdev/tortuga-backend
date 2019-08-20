<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderCollection;
use App\Order;
use Illuminate\Http\Request;
use Tortuga\CursorPaginator;
use Tortuga\Order\CreateOrderStrategy;
use Tortuga\Order\UpdateOrderStrategy;
use Tortuga\Validation\InvalidDataException;
use Tortuga\Validation\JsonSchemaValidator;
use Tortuga\Validation\OrderSlotFullyBookedException;
use App\Http\Resources\Order as OrderResource;

class OrderController extends Controller
{
    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    /**
     * CustomerRegistrationStrategy constructor.
     * @param JsonSchemaValidator $validator
     */
    public function __construct(JsonSchemaValidator $validator)
    {
        $this->validator = $validator;
        $this->middleware('auth:api')->only(['index', 'update']);
    }

    /**
     * @param Request $request
     * @return OrderResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            /** @var CreateOrderStrategy $strategy */
            $strategy = app()->make(CreateOrderStrategy::class);

            $order = $strategy->createOrder(json_decode($request->getContent()));

            return new OrderResource($order);
        } catch (InvalidDataException $e) {
            return $this->_returnError(422, 'JSON Schema Validation error', $e->getMessage(), $e->getDataPointer());
        } catch (OrderSlotFullyBookedException $e) {
            return $this->_returnError(
                409,
                'Order Time unacceptable.',
                'Selected Order Time is fully booked. Please, choose a different slot.',
                '/data/attributes/order_time'
            );
        }
    }

    /**
     * @param Request $request
     * @return OrderCollection
     */
    public function index(Request $request)
    {
        $builder = Order::with(['items', 'customer'])
            ->where('status', '<>', 'incomplete');

        $limit = $request->get('limit', 5);

        // first load - from now
        if (!request('before') && !request('after')) {
            $builder = $builder->fromNow();
        }

        // default is AFTER with  no cursor set
        // see the builder macro in CursorPaginationServiceProvider::class
        $direction = request('before') ? 'desc' : 'asc';

        /** @var CursorPaginator $orders */
        $orders = $builder->cursorPaginate($limit, [
            'order_time' => $direction,
            'id'         => $direction,
        ])->appends(['limit' => $limit]);

        return new OrderCollection($orders->items(), $orders);
    }

    /**
     * @param Order   $order
     * @param Request $request
     * @return OrderResource|\Illuminate\Http\JsonResponse
     */
    public function update(Order $order, Request $request)
    {
        try {
            /** @var UpdateOrderStrategy $strategy */
            $strategy = app()->make(UpdateOrderStrategy::class);

            $order = $strategy->updateOrder($order, json_decode($request->getContent()));

            return new OrderResource($order);
        } catch (InvalidDataException $e) {
            return $this->_returnError(422, 'JSON Schema Validation error', $e->getMessage(), $e->getDataPointer());
        } catch (OrderSlotFullyBookedException $e) {
            return $this->_returnError(
                409,
                'Order Time unacceptable.',
                'Selected Order Time is fully booked. Please, choose a different slot.',
                '/data/attributes/order_time'
            );
        }
    }
}
