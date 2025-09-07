<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePopAnnouncementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pop_announcements', function (Blueprint $table) {
            $table->id();   
            $table->string( 'en_title' )->nullable();
            $table->string( 'zh_title' )->nullable();
            $table->string( 'image' )->nullable();
            $table->text( 'en_text' )->nullable();
            $table->text( 'zh_text' )->nullable();
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
        Schema::dropIfExists('pop_announcements');
    }
}
