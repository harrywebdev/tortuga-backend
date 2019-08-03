<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Category extends JsonResource
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
            'type'       => 'categories',
            'attributes' => [
                'emoji'      => $this->emoji,
                'sequence'   => $this->sequence,
                'title'      => $this->title,
                'slug'       => $this->slug,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }
}
