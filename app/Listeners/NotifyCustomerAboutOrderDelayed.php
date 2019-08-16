<?php

namespace App\Listeners;

use App\Events\OrderDelayed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerAboutOrderDelayed extends LoggingListener
{
    /**
     * Handle the event.
     *
     * @param  OrderDelayed $event
     * @return void
     */
    public function handle(OrderDelayed $event)
    {
        // dispatch with no delay since there's no way currently how to undo the delay
        \App\Jobs\NotifyCustomerAboutOrderDelayed::dispatch($event->getOrder());
    }
}
