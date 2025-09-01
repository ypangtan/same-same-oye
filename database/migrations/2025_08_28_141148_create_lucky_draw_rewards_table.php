<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLuckyDrawRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucky_draw_rewards', function (Blueprint $table) {
            $table->id();
            $table->string( 'customer_member_id' )->nullable();
            $table->string( 'name' )->nullable();
            $table->decimal( 'quantity', 10, 0 )->nullable();
            $table->string( 'reference_id' )->nullable();
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
        Schema::dropIfExists('lucky_draw_rewards');
    }
}
