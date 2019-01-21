<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedInteger('product_id');

            $table->boolean('active')->default(false);
            $table->mediumInteger('sequence')->default(0);

            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedMediumInteger('price');
            $table->string('currency', 3)->default('CZK');

            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('parent_id')->references('id')->on('product_variations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_variations');
    }
}
