<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlaylistIdToPlaylistCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('playlist_categories', function (Blueprint $table) {
            $table->foreignId('playlist_id')->nullable()->constrained('playlists')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onUpdate( 'restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('playlist_categories', function (Blueprint $table) {
            $table->dropForeign( ['playlist_id'] );
            $table->dropForeign( ['category_id'] );
            $table->dropColumn( 'playlist_id' );
            $table->dropColumn( 'category_id' );
        });
    }
}
