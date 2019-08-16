<?php

namespace App\Listeners;

use App\Events\OrderMarkedAsReadyForPickup;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerAboutOrderReady extends LoggingListener
{
    /**
     * Handle the event.
     *
     * @param  OrderMarkedAsReadyForPickup $event
     * @return void
     */
    public function handle(OrderMarkedAsReadyForPickup $event)
    {
        // dispatch a *delayed* Job here, reasons are:
        // delay is so we don't send unnecessary text messages and Laravel listener
        // can't do that
        \App\Jobs\NotifyCustomerAboutOrderReady::dispatch(
            $event->getOrder())->delay(now()->addSeconds(config('tortuga.notifications_delay'))
        );
    }
}
