<?php

namespace Tortuga\ApiTransformer;

class ProductsApiTransformer implements ApiTransformer
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

            $variations = $item['variations'];
            unset($item['id'], $item['variations']);

            $outputItem['attributes']    = $this->dasherizeKeys($item);
            $outputItem['relationships'] = ['variations' => ['data' => []]];

            foreach ($variations as $variation) {
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

                $variationItem['attributes'] = $this->dasherizeKeys($variation);
                $output['included'][]        = $variationItem;
            }

            $output['data'][] = $outputItem;
        }

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