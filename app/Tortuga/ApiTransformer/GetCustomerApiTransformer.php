<?php

namespace Tortuga\ApiTransformer;

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
                'type'       => 'customer',
                'attributes' => $this->dasherizeKeys($data),
            ],
            'links' => [
                'self' => env('APP_URL') . '/api/customer/' . $data['id'],
            ],
        ];

        return $output;
    }

    /**
     * Swap underscore for dash in attribute keys
     * @param array $attributes
     * @return array
     */
    private function dasherizeKeys(array $attributes): array
    {
        foreach ($attributes as $key => $attribute) {
            if (strpos($key, '_')) {
                $attributes[str_replace('_', '-', $key)] = $attribute;
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }
}