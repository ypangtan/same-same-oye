<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_records', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('facebook_name')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('live_id')->nullable();
            $table->longtext('product_metas')->nullable();
            $table->float('total_price', 12, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('handler')->nullable();
            $table->string('remarks')->nullable();
            $table->string('reference')->nullable();
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
        Schema::dropIfExists('sales_records');
    }
}
