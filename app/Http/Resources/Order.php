<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id'         => $this->id,
            'type'       => 'orders',
            'attributes' => [
                'hash_id'                => $this->hash_id,
                'delivery_type'          => $this->delivery_type,
                'payment_type'           => $this->payment_type,
                'order_time'             => $this->order_time,
                'is_takeaway'            => $this->is_takeaway,
                'status'                 => $this->status,
                'total_amount'           => $this->total_amount,
                'subtotal_amount'        => $this->subtotal_amount,
                'delivery_amount'        => $this->delivery_amount,
                'extra_amount'           => $this->extra_amount,
                'currency'               => $this->currency,
                'is_delayed'             => $this->is_delayed,
                'is_changed'             => $this->is_changed,
                'changed_reason'         => $this->changed_reason,
                'rejected_reason'        => $this->rejected_reason,
                'cancelled_reason'       => $this->cancelled_reason,
                'created_at'             => $this->created_at,
                'updated_at'             => $this->updated_at,
                'formatted_total_amount' => $this->total_amount_formatted,
            ],
        ];

        // add relationships
        // * Customer (always)
        // * Order Items (if requested)
        $data['relationships'] = [
            'customer'    => [
                'data' => [
                    'id'   => $this->customer_id,
                    'type' => 'customers',
                ],
            ],
            'order-items' => ['data' => []],
        ];

        foreach ($this->items as $item) {
            $data['relationships']['order-items']['data'][] = [
                'id'   => $item->id,
                'type' => 'order-items',
            ];
        }

        return $data;
    }
}
