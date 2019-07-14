<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetCustomersApiTransformer implements ApiTransformer
{
    /**
     * @param array $data
     * @return array
     */
    public function output(array $data): array
    {
        $output = [
            'data'  => [],
            'links' => [
                'self' => env('APP_URL') . '/api/customers',
            ],
        ];

        foreach ($data as $customer) {
            $output['data'][] =
                [
                    'id'         => $customer['id'],
                    'type'       => 'customers',
                    'attributes' => Arr::except($customer, 'id'),
                ];
        }

        return $output;
    }
}
