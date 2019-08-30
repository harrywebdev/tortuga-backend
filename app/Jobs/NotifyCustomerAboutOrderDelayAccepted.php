<?php

namespace App\Jobs;

use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Tortuga\Customer\CustomerCommunicator;

class NotifyCustomerAboutOrderDelayAccepted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Order
     */
    private $order;

    /**
     * Create a new job instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @param CustomerCommunicator $communicator
     * @return void
     */
    public function handle(CustomerCommunicator $communicator)
    {
        // only send message if the status is still what we need
        $communicator->sendOrderDelayAcceptedNotification($this->order->customer, $this->order);
    }
}
