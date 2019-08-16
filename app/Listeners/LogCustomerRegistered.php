<?php

namespace App\Listeners;

use App\Events\CustomerRegistered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogCustomerRegistered extends LoggingListener implements ShouldQueue
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
     * @param  CustomerRegistered $event
     * @return void
     */
    public function handle(CustomerRegistered $event)
    {
        $customer = $event->getCustomer();

        Log::info('Customer registered.', [
            'reg_type'       => $customer->reg_type,
            'name'           => $customer->name,
            'mobile_number'  => !$customer->mobile_number ?: $this->_censorString($customer->mobile_number),
            'account_kit_id' => !$customer->account_kit_id ?: $this->_censorString($customer->account_kit_id),
        ]);
    }
}
