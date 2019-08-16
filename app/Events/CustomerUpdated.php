<?php

namespace App\Events;

use App\Customer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CustomerUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var object
     */
    private $oldCustomer;

    /**
     * Create a new event instance.
     *
     * @param Customer $customer
     * @param object   $oldCustomer
     */
    public function __construct(Customer $customer, object $oldCustomer)
    {
        $this->customer    = $customer;
        $this->oldCustomer = $oldCustomer;
    }

    /**
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return object
     */
    public function getOldCustomer(): object
    {
        return $this->oldCustomer;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
