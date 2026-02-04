<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDurationInMonthsToSubscriptionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('months_to_subscription_plans', function (Blueprint $table) {
            $table->integer('duration_in_months')->default(0);
            $table->integer('duration_in_years')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('months_to_subscription_plans', function (Blueprint $table) {
            $table->dropColumn( 'duration_in_months' );
            $table->dropColumn( 'duration_in_years' );
        });
    }
}
