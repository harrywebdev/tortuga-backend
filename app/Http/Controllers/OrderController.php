<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tortuga\Order\OrderCreationStrategy;
use Tortuga\Validation\InvalidDataException;

class OrderController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            /** @var OrderCreationStrategy $strategy */
            $strategy = app()->make(OrderCreationStrategy::class);

            $order = $strategy->createOrder(json_decode($request->getContent()));
            dd($order);
//            $transformer = new GetOrderApiTransformer();

//            return response()->json($transformer->output($order->toArray()));
        } catch (InvalidDataException $e) {
            return response()->json((object)['errors' => [(object)[
                'status' => 422,
                'source' => (object)['pointer' => $e->getDataPointer()],
                'title'  => 'JSON Schema Validation error',
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
