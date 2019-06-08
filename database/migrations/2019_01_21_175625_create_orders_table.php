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
            $table->string('pickup_time', 5)->nullable();

            $table->enum('status', OrderStatus::keys())->default(OrderStatus::INCOMPLETE());
            $table->boolean('is_auto_accepted')->default(false);
            $table->boolean('is_overload')->default(false);

            $table->unsignedMediumInteger('total_amount');
            $table->unsignedMediumInteger('subtotal_amount');
            $table->unsignedMediumInteger('delivery_amount');
            $table->unsignedMediumInteger('extra_amount');
            $table->string('currency', 3)->default('CZK');

            $table->boolean('is_touched')->default(false);
            $table->string('touched_reason')->nullable();
            $table->string('reject_reason')->nullable();
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
