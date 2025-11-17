<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPlaylistItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_playlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_playlist_id')->nullable()->constrained('user_playlists')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('items')->onUpdate( 'restrict')->onDelete('cascade');
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
        Schema::dropIfExists('ads');
    }
}
