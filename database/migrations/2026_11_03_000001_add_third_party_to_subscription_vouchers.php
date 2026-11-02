<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{
    DB,
    Schema,
};

class AddThirdPartyToSubscriptionVouchers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_vouchers', function (Blueprint $table) {
            $table->tinyInteger( 'type' )->default(1)->after( 'name' )->comment( '1:subscription, 2:third party' );
        });

        Schema::create('subscription_voucher_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'subscription_voucher_id' )->constrained( 'subscription_vouchers' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->text( 'url' );
            $table->unsignedInteger( 'max_users' )->nullable()->comment( 'null: unlimited' );
            $table->tinyInteger( 'status' )->default(10);
            $table->timestamps();
        });

        // Third party voucher has no code, only url
        DB::statement( 'ALTER TABLE subscription_voucher_codes MODIFY code VARCHAR(32) NULL' );

        Schema::table('subscription_voucher_codes', function (Blueprint $table) {
            $table->foreignId( 'subscription_voucher_url_id' )->nullable()->after( 'user_subscription_id' )->constrained( 'subscription_voucher_urls' )->onUpdate( 'restrict' )->onDelete( 'set null' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_voucher_codes', function (Blueprint $table) {
            $table->dropForeign( [ 'subscription_voucher_url_id' ] );
            $table->dropColumn( 'subscription_voucher_url_id' );
        });

        DB::table( 'subscription_voucher_codes' )->whereNull( 'code' )->delete();
        DB::statement( 'ALTER TABLE subscription_voucher_codes MODIFY code VARCHAR(32) NOT NULL' );

        Schema::dropIfExists('subscription_voucher_urls');

        Schema::table('subscription_vouchers', function (Blueprint $table) {
            $table->dropColumn( 'type' );
        });
    }
}
