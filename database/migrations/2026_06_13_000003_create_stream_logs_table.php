<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStreamLogsTable extends Migration
{
    public function up()
    {
        Schema::create('stream_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onUpdate( 'restrict')->onDelete('cascade');
            $table->tinyInteger( 'content_type' )->nullable();
            $table->foreignId('radio_id')->nullable()->constrained('radios')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('items')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('playlist_id')->nullable()->constrained('playlists')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('collection_id')->nullable()->constrained('collections')->onUpdate( 'restrict')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stream_logs');
    }
}
