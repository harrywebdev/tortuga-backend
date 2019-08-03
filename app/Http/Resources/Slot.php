<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Slot extends JsonResource
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
            'id'         => $this->resource['id'],
            'type'       => 'slots',
            'attributes' => [
                'datetime' => $this->resource['datetime'],
                'slot'     => $this->resource['slot'],
            ],
        ];
    }
}
