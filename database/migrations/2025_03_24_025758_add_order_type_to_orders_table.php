<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderTypeToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->tinyInteger('order_type')->nullable()->default(1);
            $table->string('machine_reference')->nullable();
            $table->decimal('machine_total_price', 12, 2)->nullable()->default(0);
            $table->decimal('machine_discount', 12, 2)->nullable()->default(0);
            $table->decimal('machine_tax', 12, 2)->nullable()->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('order_type');
            $table->dropColumn('machine_reference');
            $table->dropColumn('machine_total_price');
            $table->dropColumn('machine_discount');
            $table->dropColumn('machine_tax');
        });
    }
}
