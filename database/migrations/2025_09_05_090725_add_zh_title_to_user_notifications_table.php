<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZhTitleToUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->string( 'en_title' )->nullable()->after( 'title' );
            $table->string( 'zh_title' )->nullable()->after( 'title' );
            $table->string( 'en_content' )->nullable()->after( 'content' );
            $table->string( 'zh_content' )->nullable()->after( 'content' );
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
            $table->dropColumn( 'en_title' );
            $table->dropColumn( 'zh_title' );
            $table->dropColumn( 'en_content' );
            $table->dropColumn( 'zh_content' );
        });
    }
}
