<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionVoucherCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_voucher_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'subscription_voucher_id' )->constrained( 'subscription_vouchers' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'user_id' )->nullable()->constrained( 'users' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->foreignId( 'user_notification_id' )->nullable()->constrained( 'user_notifications' )->onUpdate( 'restrict' )->onDelete( 'set null' );
            $table->foreignId( 'user_subscription_id' )->nullable()->constrained( 'user_subscriptions' )->onUpdate( 'restrict' )->onDelete( 'set null' );
            $table->string( 'code', 32 )->unique();
            $table->timestamp( 'claimed_at' )->nullable();
            $table->tinyInteger( 'status' )->default(1)->comment( '1:available, 10:claimed, 20:voided' );
            $table->timestamps();

            $table->index( [ 'user_notification_id', 'user_id' ] );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_voucher_codes');
    }
}
