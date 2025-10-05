<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlaylistItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->nullable()->constrained('playlists')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('items')->onUpdate( 'restrict')->onDelete('cascade');
            $table->tinyInteger( 'priority' )->default(0);
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
        Schema::dropIfExists('play_list_items');
    }
}
