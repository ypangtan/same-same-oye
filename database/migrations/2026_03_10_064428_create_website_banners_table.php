<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('website_banners', function (Blueprint $table) {
            $table->id();
            $table->string( 'en_name' )->nullable();
            $table->string( 'zh_name' )->nullable();
            $table->string( 'en_desc' )->nullable();
            $table->string( 'zh_desc' )->nullable();
            $table->string( 'image' )->nullable();
            $table->unsignedInteger( 'priority' );
            $table->tinyInteger('sequence')->nullable()->default(0);
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
        Schema::dropIfExists('website_banners');
    }
}
