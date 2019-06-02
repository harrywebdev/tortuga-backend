<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');

            $table->enum('reg_type', ['email', 'phone', 'facebook']);
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('facebook_id')->nullable();
            $table->json('meta')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['email', 'mobile']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
