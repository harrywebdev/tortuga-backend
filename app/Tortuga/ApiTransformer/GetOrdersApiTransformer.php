<?php

namespace Tortuga\ApiTransformer;

use Illuminate\Support\Arr;

class GetOrdersApiTransformer implements ApiTransformer
{
    /**
     * @param array $data
     * @return array
     */
    public function output(array $data): array
    {
        // TODO: refactor into proper Locale based on request headers
        setlocale(LC_MONETARY, 'cs_CZ');

        $output = ['data' => [], 'included' => []];
        foreach ($data as $item) {
            $outputItem = [
                'id'   => $item['id'],
                'type' => 'orders',
            ];

            $outputItem['attributes']           = Arr::except($item, ['id', 'items', 'customer']);
            $outputItem['attributes']['status'] = mb_strtolower($outputItem['attributes']['status']);

            $outputItem['attributes']['formatted_total_amount'] =
                money_format('%.0n', $outputItem['attributes']['total_amount'] / 100);

            $outputItem['relationships'] = [
                'order-items' => ['data' => []],
                'customer'    => ['data' => ['id' => $item['customer']['id'], 'type' => 'customers']],
            ];

            foreach ($item['items'] as $orderItem) {
                $orderItemItem = [
                    'id'   => $orderItem['id'],
                    'type' => 'order-items',
                ];

                $outputItem['relationships']['order-items']['data'][] = $orderItemItem;

                // let's add it to `included` too
                unset($orderItem['id']);

                // format price
                $orderItem['formatted_price']       = money_format('%.0n', $orderItem['price'] / 100);
                $orderItem['formatted_total_price'] = money_format('%.0n', $orderItem['total_price'] / 100);
                $orderItem['formatted_currency']    = localeconv()['currency_symbol'];
                unset($orderItem['currency']);

                $orderItemItem['attributes'] = $orderItem;
                $output['included'][]        = $orderItemItem;
            }

            $output['included'][] = ['id'         => $item['customer']['id'], 'type' => 'customers',
                                     'attributes' => Arr::except($item['customer'], ['id'])];
            $output['data'][]     = $outputItem;
        }

        $output['links'] = [
            'self' => env('APP_URL') . '/api/products',
        ];

        return $output;
    }
}
