<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class OrderCollection extends ResourceCollection
{
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
                'self' => env('APP_URL') . '/api/orders',
            ],
        ];

        // add included data
        // * Customer (always)
        // * Order Items (if requested)
        $data['included']  = Collection::make([]);
        $includeOrderItems = $request->get('include') && strpos($request->get('include'), 'order-items') >= 0;

        foreach ($this->collection as $order) {
            // add customer
            $data['included']->add(Customer::make($order->customer));

            if ($includeOrderItems) {
                foreach ($order->items as $item) {
                    $data['included']->add(OrderItem::make($item));
                }
            }
        }

        $data['included'] = $data['included']->unique()->values();

        return $data;
    }
}
