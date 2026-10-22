<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFestiveAnimationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('festive_animations', function (Blueprint $table) {
            $table->id();
            $table->string( 'en_title' )->nullable();
            $table->string( 'zh_title' )->nullable();
            $table->string( 'file' )->nullable();
            $table->string( 'file_name' )->nullable();
            $table->tinyInteger( 'file_type' )->default(4)->comment( '1:image, 2:gif, 3:video, 4:other' );
            $table->string( 'url' )->nullable();
            $table->date( 'start_date' )->nullable();
            $table->date( 'end_date' )->nullable();
            $table->integer( 'sequence' )->default(0);
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
        Schema::dropIfExists('festive_animations');
    }
}
