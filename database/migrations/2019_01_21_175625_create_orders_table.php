<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

            $table->enum('status', [
                'incomplete', 'received', 'rejected', 'accepted', 'processing', 'done', 'delivered', 'failed',
            ])->default('incomplete');

            $table->unsignedMediumInteger('subtotal_amount');
            $table->unsignedMediumInteger('delivery_amount');
            $table->unsignedMediumInteger('extra_amount');
            $table->unsignedMediumInteger('total_amount');
            $table->string('currency', 3)->default('CZK');

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
