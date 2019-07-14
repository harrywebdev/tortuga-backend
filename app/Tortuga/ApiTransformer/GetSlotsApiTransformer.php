<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetSlotsApiTransformer implements ApiTransformer
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
                'type' => 'slots',
            ];

            $outputItem['attributes'] = Arr::except($item, ['id']);

            $output['data'][] = $outputItem;
        }

        $output['links'] = [
            'self' => env('APP_URL') . '/api/slots',
        ];

        return $output;
    }
}
