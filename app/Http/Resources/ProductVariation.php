<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariation extends JsonResource
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
            'type'       => 'variations',
            'attributes' => [
                "active"             => $this->active,
                "sequence"           => $this->sequence,
                "title"              => $this->title,
                "slug"               => $this->slug,
                "description"        => $this->description,
                "price"              => $this->price,
                "created_at"         => $this->created_at,
                "updated_at"         => $this->updated_at,
                "formatted_price"    => $this->price_formatted,
                "formatted_currency" => $this->currency_formatted,
            ],
        ];
    }
}
