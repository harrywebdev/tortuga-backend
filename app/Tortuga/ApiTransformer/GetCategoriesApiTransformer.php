<?php

namespace Tortuga\ApiTransformer;

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

            unset($item['id'], $item['parent_id']);
            $outputItem['attributes'] = $item;

            $output['data'][] = $outputItem;
        }

        return $output;
    }
}