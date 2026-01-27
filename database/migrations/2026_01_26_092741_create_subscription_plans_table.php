<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string( 'name' )->nullable();
            $table->text( 'description' )->nullable();
            $table->decimal( 'price', 18, 2 )->default(0);
            $table->integer( 'duration_in_days' )->default(0);
            $table->string( 'ios_product_id' )->nullable();
            $table->string( 'android_product_id' )->nullable();
            $table->string( 'huawei_product_id' )->nullable();
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}
