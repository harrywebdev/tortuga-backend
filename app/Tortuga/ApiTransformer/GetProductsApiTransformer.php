<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetProductsApiTransformer implements ApiTransformer
{
    /**
     * @param array $data
     * @return array
     */
    public function output(array $data): array
    {
        $output = ['data' => [], 'included' => []];
        foreach ($data as $item) {
            $outputItem = [
                'id'   => $item['id'],
                'type' => 'products',
            ];

            $outputItem['attributes']    = Arr::except($item, ['id', 'variations']);
            $outputItem['relationships'] = ['variations' => ['data' => []]];

            foreach ($item['variations'] as $variation) {
                $variationItem = [
                    'id'   => $variation['id'],
                    'type' => 'variations',
                ];

                $outputItem['relationships']['variations']['data'][] = $variationItem;

                // let's add it to `included` too
                unset($variation['id']);

                // format price
                // TODO: refactor into proper Locale based on request headers
                setlocale(LC_MONETARY, 'cs_CZ');
                $variation['formatted_price']    = money_format('%.0n', $variation['price'] / 100);
                $variation['formatted_currency'] = localeconv()['currency_symbol'];
                unset($variation['currency']);

                $variationItem['attributes'] = $variation;
                $output['included'][]        = $variationItem;
            }

            $output['data'][] = $outputItem;
        }

        $output['links'] = [
            'self' => env('APP_URL') . '/api/products',
        ];

        return $output;
    }
}
