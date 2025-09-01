<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLuckyDrawImportHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucky_draw_import_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId( 'uploaded_by' )->nullable()->constrained( 'administrators' )->onUpdate( 'restrict' )->onDelete( 'cascade' );
            $table->string( 'name' )->nullable();
            $table->string( 'hash_name' )->nullable();
            $table->string( 'file' )->nullable();
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
        Schema::dropIfExists('lucky_draw_import_histories');
    }
}
