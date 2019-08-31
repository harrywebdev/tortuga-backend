<?php

use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \factory(App\Order::class, 1)->create()->each(function ($order) {
            $order->items()->saveMany(\factory(App\OrderItem::class, rand(1, 3))->make());

            // set totals
            $total                  = $order->items()->sum('total_price');
            $order->subtotal_amount = $total;
            $order->total_amount    = $total;
            $order->save();

            event(new \App\Events\OrderReceived($order));
        });
    }
}
