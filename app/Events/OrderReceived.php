<?php

namespace App\Events;

use App\Http\Resources\OrderCollection;
use App\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Tortuga\CursorPaginator;

class OrderReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Order
     */
    private $order;

    /**
     * Create a new event instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): Channel
    {
        return new Channel('orders');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'order.received';
    }

    /**
     * Get the data to broadcast. Need to append actual pagination links
     * so the client can have working reliable record.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        // create query that starts and finishes with our only record
        // and attach the pagination links there

        $builder = Order::with(['items', 'customer'])->where('id', '=', $this->order->id);

        /** @var CursorPaginator $orders */
        $orders = $builder->cursorPaginate(1, [
            'order_time' => 'asc',
            'id'         => 'asc',
        ])->appends(['limit' => 5]);

        $collection = new OrderCollection($orders->items(), $orders);
        return $collection->resolve();
    }
}
