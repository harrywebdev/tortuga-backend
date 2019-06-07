<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetCustomerApiTransformer implements ApiTransformer
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
                'type'       => 'customers',
                'attributes' => Arr::except($data, 'id'),
            ],
            'links' => [
                'self' => env('APP_URL') . '/api/customers/' . $data['id'],
            ],
        ];

        return $output;
    }
}
