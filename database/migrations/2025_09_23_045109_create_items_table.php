<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('add_by')->nullable()->constrained('administrators')->onUpdate( 'restrict')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onUpdate( 'restrict')->onDelete('cascade');
            $table->string( 'title' )->nullable();
            $table->string( 'lyrics' )->nullable();
            $table->string( 'file' )->nullable();
            $table->string( 'image' )->nullable();
            $table->string( 'author' )->nullable();
            $table->tinyInteger( 'membership_level' )->default(0);
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
        Schema::dropIfExists('items');
    }
}
