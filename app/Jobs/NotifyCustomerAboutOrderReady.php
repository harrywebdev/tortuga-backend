<?php

namespace App\Jobs;

use App\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Tortuga\Customer\CustomerCommunicator;
use Tortuga\Order\OrderStatus;

class NotifyCustomerAboutOrderReady implements ShouldQueue
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
     * @return void
     */
    public function handle(CustomerCommunicator $communicator)
    {
        // only send message if the status is still what we need
        if ($this->order->status == OrderStatus::MADE()) {
            $communicator->sendOrderReadyNotification($this->order->customer, $this->order);
            return;
        }
    }
}
