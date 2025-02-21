<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsProcessedToOrdersTable extends Migration
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
            $table->tinyInteger('is_processed')->default(0);
        });

        Schema::table('topup_records', function (Blueprint $table) {
            //
            $table->tinyInteger('is_processed')->default(0);
        });

        Schema::table('user_bundle_transactions', function (Blueprint $table) {
            //
            $table->tinyInteger('is_processed')->default(0);
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
            $table->dropColumn('is_processed');
        });

        Schema::table('topup_records', function (Blueprint $table) {
            //
            $table->dropColumn('is_processed');
        });

        Schema::table('user_bundle_transactions', function (Blueprint $table) {
            //
            $table->dropColumn('is_processed');
        });
    }
}
