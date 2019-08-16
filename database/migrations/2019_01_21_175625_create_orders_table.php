<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Tortuga\Order\OrderStatus;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');

            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->enum('payment_type', ['cash', 'card'])->default('cash');
            $table->dateTime('order_time');

            $table->enum('status', OrderStatus::values())->default(OrderStatus::INCOMPLETE());

            $table->unsignedMediumInteger('total_amount');
            $table->unsignedMediumInteger('subtotal_amount');
            $table->unsignedMediumInteger('delivery_amount');
            $table->unsignedMediumInteger('extra_amount');
            $table->string('currency', 3)->default('CZK');

            $table->boolean('is_delayed')->default(false);
            $table->boolean('is_changed')->default(false);
            $table->string('changed_reason')->nullable();
            $table->string('rejected_reason')->nullable();
            $table->string('cancelled_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
