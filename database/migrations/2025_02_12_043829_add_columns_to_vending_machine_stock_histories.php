<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToVendingMachineStockHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vending_machine_stock_histories', function (Blueprint $table) {
            //
            $table->foreignId('vending_machine_stock_id')->nullable()->constrained('vending_machine_stocks')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('froyo_id')->nullable()->constrained('froyos')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('syrup_id')->nullable()->constrained('syrups')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('topping_id')->nullable()->constrained('toppings')->onUpdate( 'restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vending_machine_stock_histories', function (Blueprint $table) {
            //
        });
    }
}
