<?php

namespace Tortuga\Order;

use App\Events\OrderReceived;
use App\Order;
use App\OrderItem;
use App\ProductVariation;
use Illuminate\Support\Facades\Log;
use Tortuga\AppSettings;
use Tortuga\SlotStrategy;
use Tortuga\Validation\JsonSchemaValidator;
use Tortuga\Validation\OrderSlotFullyBookedException;

class CreateOrderStrategy
{
    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    /**
     * @var SlotStrategy
     */
    private $slotStrategy;

    /**
     * @var AppSettings
     */
    private $settings;

    /**
     * CustomerRegistrationStrategy constructor.
     * @param JsonSchemaValidator $validator
     * @param SlotStrategy        $slotStrategy
     * @param AppSettings         $settings
     */
    function __construct(JsonSchemaValidator $validator, SlotStrategy $slotStrategy, AppSettings $settings)
    {
        $this->validator    = $validator;
        $this->slotStrategy = $slotStrategy;
        $this->settings     = $settings;
    }

    /**
     * @param object $orderData
     * @return Order
     * @throws OrderSlotFullyBookedException
     */
    public function createOrder(object $orderData): Order
    {
        $this->validator->validate(
            $orderData,
            'http://localhost/create_order.json'
        );

        $orderTime = $this->slotStrategy->createOrderTimeFromShortString($orderData->data->attributes->order_time);

        // check if desired slot is available
        if (!$this->slotStrategy->isSlotAvailable($orderTime)) {
            throw new OrderSlotFullyBookedException();
        }

        $order                = new Order();
        $order->customer_id   = $orderData->data->relationships->customer->data->id;
        $order->delivery_type = $orderData->data->attributes->delivery_type;
        $order->payment_type  = $orderData->data->attributes->payment_type;

        $order->order_time      = $orderTime;
        $order->is_takeaway     = $orderData->data->attributes->is_takeaway;
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

        event(new OrderReceived($order));

        return $order;
    }
}