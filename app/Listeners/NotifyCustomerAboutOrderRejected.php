<?php

namespace App\Listeners;

use App\Events\OrderRejected;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerAboutOrderRejected extends LoggingListener
{
    /**
     * Handle the event.
     *
     * @param  OrderRejected $event
     * @return void
     */
    public function handle(OrderRejected $event)
    {
        // dispatch a *delayed* Job here, reasons are:
        // delay is so we don't send unnecessary text messages
        // and Laravel listener can't do that
        \App\Jobs\NotifyCustomerAboutOrderRejected::dispatch(
            $event->getOrder())->delay(now()->addSeconds(config('tortuga.notifications_delay'))
        );
    }
}
