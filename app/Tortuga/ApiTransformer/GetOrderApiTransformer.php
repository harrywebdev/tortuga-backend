<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetOrderApiTransformer implements ApiTransformer
{
    /**
     * @param array $data
     * @return array
     */
    public function output(array $data): array
    {
        $output = [
            'data'  => [
                'id'         => $data['id'],
                'type'       => 'orders',
                'attributes' => Arr::except($data, 'id'),
            ],
            'links' => [
                'self' => env('APP_URL') . '/api/orders/' . $data['id'],
            ],
        ];

        $output['data']['attributes']['status'] = mb_strtolower($output['data']['attributes']['status']);

        return $output;
    }
}
