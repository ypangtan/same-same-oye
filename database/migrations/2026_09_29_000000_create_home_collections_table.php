<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->onUpdate( 'restrict')->onDelete('cascade');
            $table->tinyInteger( 'display_type' )->nullable();
            $table->integer( 'priority' )->default(0);
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
        Schema::dropIfExists('home_collections');
    }
}
