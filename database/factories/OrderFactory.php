<?php

use Faker\Generator as Faker;

$factory->define(\App\Order::class, function (Faker $faker) {
    $customer  = \App\Customer::orderByRaw("RAND()")->first();
    $orderTime = \Carbon\Carbon::now()->addHour(rand(1, 3));

    return [
        'customer_id'     => $customer->id,
        'delivery_type'   => 'pickup',
        'payment_type'    => 'cash',
        'order_time'      => $orderTime->format('Y-m-d H:' . (rand(0, 1) * 30) . ':00'),
        'is_takeaway'     => rand(0, 1),
        'status'          => \Tortuga\Order\OrderStatus::RECEIVED(),
        'currency'        => 'CZK',
        'total_amount'    => 0,
        'subtotal_amount' => 0,
        'delivery_amount' => 0,
        'extra_amount'    => 0,
        'is_delayed'      => 0,
        'is_changed'      => 0,
    ];
});
