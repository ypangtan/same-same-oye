<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachinesSalesDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_sales_datas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vending_machine_id')->nullable()->constrained('vending_machines')->onUpdate( 'restrict')->onDelete('cascade');
            $table->timestamp('sales_date')->nullable();
            
            // Sales data
            $table->integer('sales_type')->default(1); 
            $table->json('sales_metas')->nullable();
            $table->json('orders_metas')->nullable();
            $table->json('bundle_metas')->nullable();
            $table->json('voucher_metas')->nullable();
            $table->json('order_references')->nullable();
            $table->integer('total_sales')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0.00);
            $table->tinyInteger('status')->default(10);
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
        Schema::dropIfExists('machine_sales_datas');
    }
}
