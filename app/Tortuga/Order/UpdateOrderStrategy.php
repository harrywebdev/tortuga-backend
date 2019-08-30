<?php

namespace Tortuga\Order;

use App\Events\OrderDelayed;
use App\Events\OrderMarkedAsReadyForPickup;
use App\Events\OrderRejected;
use App\Order;
use Carbon\Carbon;
use Tortuga\AppSettings;
use Tortuga\SlotStrategy;
use Tortuga\Validation\JsonSchemaValidator;
use Tortuga\Validation\OrderSlotFullyBookedException;

class UpdateOrderStrategy
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
     * @param Order  $order
     * @param object $data
     * @return Order
     * @throws OrderSlotFullyBookedException
     */
    public function updateOrder(Order $order, object $data): Order
    {
        $this->validator->validate(
            $data,
            'http://localhost/update_order.json'
        );

        // update status
        $statusChanged = false;
        if ($data->data->attributes->status !== $order->status) {
            $order->status = new OrderStatus($data->data->attributes->status);
            $statusChanged = true;
        }

        // rejection?
        if ($data->data->attributes->rejected_reason !== $order->rejected_reason) {
            $order->rejected_reason = new OrderRejectReason($data->data->attributes->rejected_reason);
        }

        // cancellation?
        if ($data->data->attributes->cancelled_reason !== $order->cancelled_reason) {
            $order->cancelled_reason = new OrderCancelReason($data->data->attributes->cancelled_reason);
        }

        // update time?
        $isDelayed = false;
        $orderTime = new Carbon($data->data->attributes->order_time);
        if ($orderTime != $order->order_time) {
            // check if desired slot is available
            if (!$this->slotStrategy->isOpenForDelayedOrder($orderTime)) {
                throw new OrderSlotFullyBookedException();
            }

            // mark as Ignored so it's obvious Customer hasn't confirmed the change
            $order->status     = OrderStatus::IGNORED();
            $order->order_time = $orderTime;
            $order->is_delayed = true;
            $isDelayed         = true;
        }

        $order->save();
 
        // notifications
        if ($statusChanged && $order->status == OrderStatus::MADE()) {
            event(new OrderMarkedAsReadyForPickup($order));
        }

        if ($statusChanged && $order->status == OrderStatus::REJECTED()) {
            event(new OrderRejected($order));
        }

        if ($isDelayed) {
            event(new OrderDelayed($order));
        }

        return $order;
    }
}