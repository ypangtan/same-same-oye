<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralGiftSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_gift_settings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger( 'reward_type' )->default(1);
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onUpdate( 'restrict')->onDelete('cascade');
            $table->decimal( 'expiry_day', 5, 0 )->default(30);
            $table->decimal( 'reward_value', 10, 0 )->default(0);
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
        Schema::dropIfExists('referral_gift_settings');
    }
}
