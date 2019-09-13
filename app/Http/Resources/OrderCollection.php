<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Tortuga\CursorPaginator;

class OrderCollection extends ResourceCollection
{
    /**
     * @var null
     */
    private $paginator;

    /**
     * OrderCollection constructor.
     * @param mixed                $resource
     * @param CursorPaginator|null $paginator
     */
    public function __construct($resource, $paginator = null)
    {
        parent::__construct($resource);
        $this->paginator = $paginator;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'data'  => $this->collection,
            'links' => [
                'self' => config('app.url') . '/api/orders',
            ],
        ];

        // add correct self params
        if (count($request->all())) {
            $data['links']['self'] .= '?' . http_build_query($request->all());
        }

        // add included data
        // * Customer (always)
        // * Order Items (if requested)
        $data['included'] = Collection::make([]);

        foreach ($this->collection as $order) {
            // add customer
            $data['included']->add(Customer::make($order->customer));

            foreach ($order->items as $item) {
                $data['included']->add(OrderItem::make($item));
            }
        }

        $data['included'] = $data['included']->unique()->values();

        if ($this->paginator) {
            $data['links']['next'] = $this->paginator->nextCursorUrl(config('app.url') . '/api/orders');
            $data['links']['prev'] = $this->paginator->prevCursorUrl(config('app.url') . '/api/orders');
        }

        return $data;
    }
}
