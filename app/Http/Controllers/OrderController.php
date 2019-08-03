<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderCollection;
use App\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tortuga\Order\OrderCreationStrategy;
use Tortuga\Order\OrderStatus;
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
    }

    /**
     * @param Request $request
     * @return OrderResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            /** @var OrderCreationStrategy $strategy */
            $strategy = app()->make(OrderCreationStrategy::class);

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
        // TODO: add $queryParam for asc/desc and pagination
        $builder = Order::with(['items', 'customer'])
            ->where('status', '<>', 'incomplete')
            ->orderedByTime()
            ->fromNow();

        $orders = $builder->get();

        return new OrderCollection($orders);
    }

    /**
     * @param Order   $order
     * @param Request $request
     * @return OrderResource|\Illuminate\Http\JsonResponse
     */
    public function update(Order $order, Request $request)
    {
        try {
            $data = json_decode($request->getContent());
            $this->validator->validate(
                $data,
                'http://localhost/update_order.json'
            );

            // update status
            if ($data->data->attributes->status !== $order->status) {
                $order->status = new OrderStatus($data->data->attributes->status);
            }

            // update order time
            $orderTime = new Carbon($data->data->attributes->order_time);
            if ($orderTime != $order->order_time) {
                $order->order_time = $orderTime;
            }

            $order->save();

            return new OrderResource($order);
        } catch (InvalidDataException $e) {
            return $this->_returnError(422, 'JSON Schema Validation error', $e->getMessage(), $e->getDataPointer());
        }
    }
}
