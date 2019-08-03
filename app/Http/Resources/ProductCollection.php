<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class ProductCollection extends ResourceCollection
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
                'self' => env('APP_URL') . '/api/products',
            ],
        ];

        // add included data
        // * Customer (always)
        // * Order Items (if requested)
        $data['included']  = Collection::make([]);
        $includeVariations = $request->get('include') && strpos($request->get('include'), 'variations') >= 0;

        if ($includeVariations) {
            foreach ($this->collection as $product) {
                foreach ($product->variations as $variation) {
                    $data['included']->add(ProductVariation::make($variation));
                }
            }
        }

        $data['included'] = $data['included']->unique()->values();

        return $data;
    }
}
