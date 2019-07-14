<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;
use Tortuga\ApiTransformer\GetOrderApiTransformer;
use Tortuga\ApiTransformer\GetOrdersApiTransformer;
use Tortuga\Order\OrderCreationStrategy;
use Tortuga\Order\OrderStatus;
use Tortuga\Validation\InvalidDataException;
use Tortuga\Validation\JsonSchemaValidator;
use Tortuga\Validation\OrderSlotFullyBookedException;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            /** @var OrderCreationStrategy $strategy */
            $strategy = app()->make(OrderCreationStrategy::class);

            $order       = $strategy->createOrder(json_decode($request->getContent()));
            $transformer = new GetOrderApiTransformer();

            return response()->json($transformer->output($order->toArray()));
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // TODO: add $queryParam for asc/desc and pagination
        $builder = Order::with(['items', 'customer'])
            ->where('status', '<>', 'incomplete')
            ->orderedByTime()
            ->fromNow();

        $orders      = $builder->paginate(10)->toArray();
        $transformer = new GetOrdersApiTransformer();

        return response()->json($transformer->output($orders['data']));
    }

    /**
     * @param Order   $order
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Order $order, Request $request)
    {
        try {
            $transformer = new GetOrderApiTransformer();

            $data = json_decode($request->getContent());
            $this->validator->validate(
                $data,
                'http://localhost/update_order.json'
            );

            if ($data->data->attributes->status !== $order->status) {
                $order->status = new OrderStatus($data->data->attributes->status);
            }

            $order->save();

            return response()->json($transformer->output($order->toArray()));
        } catch (InvalidDataException $e) {
            return $this->_returnError(422, 'JSON Schema Validation error', $e->getMessage(), $e->getDataPointer());
        }
    }
}
