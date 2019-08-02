<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetSettingsApiTransformer implements ApiTransformer
{
    /**
     * @param array $data
     * @return array
     */
    public function output(array $data): array
    {
        $output = [
            'data'  => [
                'id'         => 1,
                'type'       => 'settings',
                'attributes' => Arr::except($data, 'id'),
            ],
            'links' => [
                'self' => env('APP_URL') . '/api/settings/' . 1,
            ],
        ];

        return $output;
    }
}
