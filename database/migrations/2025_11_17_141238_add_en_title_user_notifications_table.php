<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnTitleToUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->text('en_title')->nullable();
            $table->text('zh_title')->nullable();
            $table->text('en_content')->nullable();
            $table->text('zh_content')->nullable();
            $table->text( 'target_url' )->nullable();
            $table->tinyInteger( 'is_template' )->default( 0 );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playlists', function (Blueprint $table) {
            $table->dropColumn( 'is_item' );
        });
    }
}
