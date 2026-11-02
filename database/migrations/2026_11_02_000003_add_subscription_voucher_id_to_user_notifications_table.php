<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionVoucherIdToUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->foreignId( 'subscription_voucher_id' )->nullable()->after( 'user_id' )->constrained( 'subscription_vouchers' )->onUpdate( 'restrict' )->onDelete( 'set null' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropForeign( [ 'subscription_voucher_id' ] );
            $table->dropColumn( 'subscription_voucher_id' );
        });
    }
}
