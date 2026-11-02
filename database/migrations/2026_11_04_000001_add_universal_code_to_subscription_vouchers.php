<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniversalCodeToSubscriptionVouchers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_vouchers', function (Blueprint $table) {
            $table->string( 'universal_code', 32 )->nullable()->unique()->after( 'quantity' )->comment( 'One code for everyone ( e.g. share in social media ), each claim take one code from quantity' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_vouchers', function (Blueprint $table) {
            $table->dropUnique( [ 'universal_code' ] );
            $table->dropColumn( 'universal_code' );
        });
    }
}
