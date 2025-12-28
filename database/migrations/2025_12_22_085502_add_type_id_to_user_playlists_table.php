<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeIdToUserPlaylistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_playlists', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->constrained('types')->onUpdate( 'restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_playlists', function (Blueprint $table) {
            $table->dropForeign( ['type_id'] );
            $table->dropColumn( 'type_id' );
        });
    }
}
