<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBirthdayGiftSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('birthday_gift_settings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger( 'reward_type' )->default(1);
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers')->onUpdate( 'restrict')->onDelete('cascade');
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
        Schema::dropIfExists('birthday_gift_settings');
    }
}
