<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Product extends JsonResource
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
            'type'       => 'products',
            'attributes' => [
                'category_id' => $this->category_id,
                'sequence'    => $this->sequence,
                'title'       => $this->title,
                'slug'        => $this->slug,
                'heat'        => $this->heat,
                'description' => $this->description,
                'created_at'  => $this->created_at,
                'updated_at'  => $this->updated_at,
            ],
        ];

        $includeVariations = $request->get('include') && strpos($request->get('include'), 'variations') >= 0;

        if ($includeVariations) {
            // add relationships
            // * Variations (if requested)
            $data['relationships'] = [
                'variations' => [
                    'data' => [],
                ],
            ];

            foreach ($this->variations as $variation) {
                $data['relationships']['variations']['data'][] = [
                    'id'   => $variation->id,
                    'type' => 'variations',
                ];
            }
        }

        return $data;
    }
}
