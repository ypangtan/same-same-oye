<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRewardValueToRanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->decimal( 'reward_value', 10, 0 )->default(0)->after( 'target_spending' );
            $table->decimal( 'priority', 3, 0 )->default(0)->after( 'target_spending' );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ranks', function (Blueprint $table) {
            $table->dropColumn( 'reward_value' );
            $table->dropColumn( 'priority' );
        });
    }
}
