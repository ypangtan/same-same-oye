<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_items', function (Blueprint $table) {
            $table->id();
            $table->string( 'keyword' )->nullable();
            $table->foreignId('item_id')->nullable()->constrained('items')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('playlist_id')->nullable()->constrained('playlists')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('collection_id')->nullable()->constrained('collections')->onUpdate( 'restrict')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('keyword');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_items');
    }
}
