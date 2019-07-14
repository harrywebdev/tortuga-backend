<?php

use Faker\Generator as Faker;

$factory->define(\App\OrderItem::class, function (Faker $faker) {
    $product   = \App\Product::orderByRaw("RAND()")->first();
    $variation = $product->variations()->orderByRaw("RAND()")->first();
    $qty       = rand(1, 3);

    return [
        'title'       => $product->title . ' - ' . $variation->title,
        'price'       => $variation->price,
        'quantity'    => $qty,
        'total_price' => $variation->price * $qty,
        'currency'    => 'CZK',
    ];
});
