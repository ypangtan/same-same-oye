<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrendingContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trending_contents', function (Blueprint $table) {
            $table->id();
            $table->string( 'title' )->nullable();
            $table->text( 'desc' )->nullable();
            $table->tinyInteger( 'upload_type' )->default(1);
            $table->string( 'image' )->nullable();
            $table->string( 'file' )->nullable();
            $table->text( 'url' )->nullable();
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
        Schema::dropIfExists('trending_contents');
    }
}
