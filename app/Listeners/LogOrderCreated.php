<?php

namespace App\Listeners;

use App\Events\OrderReceived;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogOrderCreated extends LoggingListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object $event
     * @return void
     */
    public function handle(OrderReceived $event)
    {
        $order = $event->getOrder();

        Log::info('Order created.', [
            'id'                     => $order->id,
            'customer_id'            => $order->customer->id,
            'customer_name'          => $order->customer->name,
            'customer_mobile'        => !$order->customer->mobile_number ?:
                $this->_censorString($order->customer->mobile_number),
            'order_time'             => $order->order_time,
            'is_takeaway'            => $order->is_takeaway,
            'total_amount_formatted' => $order->total_amount_formatted,
            'total_items'            => $order->items()->count(),
        ]);
    }
}
