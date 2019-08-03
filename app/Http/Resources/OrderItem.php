<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'type'       => 'order-items',
            'attributes' => [
                "title"                 => $this->title,
                "price"                 => $this->price,
                "quantity"              => $this->quantity,
                "total_price"           => $this->total_price,
                "created_at"            => $this->created_at,
                "updated_at"            => $this->updated_at,
                "formatted_price"       => $this->price_formatted,
                "formatted_total_price" => $this->total_price_formatted,
                "formatted_currency"    => $this->currency_formatted,
            ],
        ];
    }
}
