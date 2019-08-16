<?php

namespace App\Listeners;

use App\Events\CustomerUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogCustomerUpdated extends LoggingListener implements ShouldQueue
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
     * @param  CustomerUpdated $event
     * @return void
     */
    public function handle(CustomerUpdated $event)
    {
        $customer = $event->getCustomer();

        Log::info('Customer updated.', [
            'id'       => $customer->id,
            'old_name' => $event->getOldCustomer()->name,
            'new_name' => $customer->name,
        ]);
    }
}
