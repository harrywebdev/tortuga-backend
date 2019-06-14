<?php

namespace Tortuga\Order;

use App\Events\OrderReceived;
use App\Order;
use App\OrderItem;
use App\ProductVariation;
use Tortuga\Validation\JsonSchemaValidator;

class OrderCreationStrategy
{
    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    /**
     * CustomerRegistrationStrategy constructor.
     * @param JsonSchemaValidator $validator
     */
    function __construct(JsonSchemaValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param object $orderData
     * @return Order
     */
    public function createOrder(object $orderData): Order
    {
        $this->validator->validate(
            $orderData,
            'http://localhost/create_order.json'
        );

        $order                  = new Order();
        $order->customer_id     = $orderData->data->relationships->customer->data->id;
        $order->delivery_type   = $orderData->data->attributes->delivery_type;
        $order->payment_type    = $orderData->data->attributes->payment_type;
        $order->order_time      = $orderData->data->attributes->order_time;
        $order->status          = OrderStatus::INCOMPLETE();
        $order->subtotal_amount = 0;
        $order->total_amount    = 0;
        $order->delivery_amount = 0;
        $order->extra_amount    = 0;
        $order->currency        = 'CZK'; // TODO: locale etc
        $order->save();

        $subtotal = 0;
        foreach ($orderData->data->relationships->items->data as $item) {
            $productVariation = ProductVariation::findOrFail($item->attributes->product_variation_id);

            $orderItem              = new OrderItem();
            $orderItem->title       = $productVariation->product->title . ' - ' . $productVariation->title;
            $orderItem->price       = $productVariation->price;
            $orderItem->quantity    = $item->attributes->quantity;
            $orderItem->total_price = $orderItem->price * $orderItem->quantity;
            $orderItem->currency    = 'CZK'; // TODO: locale etc

            $subtotal += $orderItem->total_price;
            $order->items()->save($orderItem);
        }

        $order->subtotal_amount = $subtotal;
        $order->total_amount    = $order->subtotal_amount;
        $order->status          = OrderStatus::RECEIVED();
        $order->save();

        // also update Customer
        try {
            $order->customer->name = $orderData->data->relationships->customer->data->attributes->name;
            $order->customer->save();
        } catch (\Exception $e) {
            // it's not critical so just report to tracker
            // TODO: error reporting to like honeybadger
        }

        event(new OrderReceived($order));

        return $order;
    }
}