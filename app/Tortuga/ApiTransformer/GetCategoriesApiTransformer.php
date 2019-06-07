<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetCategoriesApiTransformer implements ApiTransformer
{
    /**
     * @param array $data
     * @return array
     */
    public function output(array $data): array
    {
        $output = ['data' => []];
        foreach ($data as $item) {
            $outputItem = [
                'id'   => $item['id'],
                'type' => 'categories',
            ];

            $outputItem['attributes'] = Arr::except($item, ['id', 'parent_id']);

            $output['data'][] = $outputItem;
        }

        $output['links'] = [
            'self' => env('APP_URL') . '/api/categories',
        ];

        return $output;
    }
}
