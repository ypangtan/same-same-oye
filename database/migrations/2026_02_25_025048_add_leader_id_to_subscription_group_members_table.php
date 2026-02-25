<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaderIdToSubscriptionGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_group_members', function (Blueprint $table) {
            $table->foreignId('leader_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_group_members', function (Blueprint $table) {
            $table->dropForeign( [ 'leader_id' ] );
            $table->dropColumn( 'leader_id' );
        });
    }
}
