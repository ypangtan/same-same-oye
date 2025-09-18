<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_versions', function (Blueprint $table) {
            $table->id();
            $table->decimal( 'version', 5, 2 );
            $table->tinyInteger( 'force_logout' )->default( 10 );
            $table->text( 'en_notes' )->nullable();
            $table->text( 'zh_notes' )->nullable();
            $table->text( 'en_desc' )->nullable();
            $table->text( 'zh_desc' )->nullable();
            $table->tinyInteger( 'platform' );
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
        Schema::dropIfExists('app_versions');
    }
}
